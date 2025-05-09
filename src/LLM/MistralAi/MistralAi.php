<?php

namespace AiBundle\LLM\MistralAi;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\LLMUsage;
use AiBundle\LLM\MistralAi\Dto\ChatCompletionRequest;
use AiBundle\LLM\MistralAi\Dto\ChatCompletionResponse;
use AiBundle\LLM\MistralAi\Dto\JsonSchema;
use AiBundle\LLM\MistralAi\Dto\MistralAiToolChoice;
use AiBundle\LLM\MistralAi\Dto\MistralFunction;
use AiBundle\LLM\MistralAi\Dto\MistralTool;
use AiBundle\LLM\MistralAi\Dto\ResponseFormat;
use AiBundle\LLM\MistralAi\Dto\MistralAiMessage;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolsHelper;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use AiBundle\LLM\LLMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MistralAi extends AbstractLLM {

  private const ENDPOINT = 'https://api.mistral.ai/v1';

  public function __construct(
    #[SensitiveParameter] private readonly string $apiKey,
    private readonly string $model,
    private readonly float $timeout,
    #[Autowire('@ai_bundle.rest.http_client')] private readonly HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.rest.serializer')] private readonly Serializer $serializer,
    private readonly SchemaGenerator $schemaGenerator,
    private readonly ToolsHelper $toolsHelper
  ) {}

  /**
   * @inheritDoc
   */
  public function generate(
    array $messages,
    ?GenerateOptions $options = null,
    ?string $responseDataType = null,
    ?Toolbox $toolbox = null
  ): LLMResponse {
    try {
      $format = $responseDataType ? $this->schemaGenerator->generateForClass($responseDataType) : null;
    } catch (SchemaGeneratorException $ex) {
      throw new LLMException(
        'Error generating schema for datatype: ' . $ex->getMessage(),
        previous: $ex
      );
    }

    $mistralAiMessages = array_map(fn (Message $message) => MistralAiMessage::fromMessage($message), $messages);
    $usage = LLMUsage::empty();

    $finalResponse = null;
    $firstCall = true;
    do {
      $toolbox?->ensureMaxLLMCalls($usage->llmCalls + 1);

      /** @var ChatCompletionResponse $res */
      $res = $this->doRequest(
        'POST',
        '/chat/completions',
        ChatCompletionResponse::class,
        ChatCompletionRequest::fromGenerateOptions(
          $this->model,
          $mistralAiMessages,
          $options
        )
          ->setResponseFormat($format !== null ? new ResponseFormat(
            'json_schema',
            new JsonSchema('response', $format)
          ) : null)
          ->setTools($toolbox !== null
            ? array_map(fn (Tool $tool) => new MistralTool(
              MistralFunction::fromTool($tool, $this->toolsHelper)
            ), $toolbox->getTools())
            : null
          )
          ->setToolChoice(
            $toolbox !== null && $firstCall
              ? MistralAiToolChoice::forToolbox($toolbox)
              : null
          )
      );

      $usage = $usage->add($res->usage->toLLMUsage());
      $message = $res->choices[0]->message;

      if (!empty($message->toolCalls)) {
        $mistralAiMessages[] = $message;
        foreach ($message->toolCalls as $toolCall) {
          if (($tool = $toolbox->getTool($toolCall->function->name)) === null) {
            throw new LLMException(sprintf(
              'Got tool call for tool %s but no such tool is registered in the toolbox.',
              $toolCall->function->name
            ));
          }
          $toolRes = $this->toolsHelper->callTool($tool, $toolCall->function->arguments);
          $mistralAiMessages[] = new MistralAiMessage(
            'tool',
            $toolRes,
            name: $tool->name,
            toolCallId: $toolCall->id,
          );
        }
      } else {
        try {
          $dataObject = $format !== null
            ? $this->serializer->deserialize($message->content, $responseDataType, 'json')
            : null;
        } catch (SerializerExceptionInterface) {
          $dataObject = null;
        }

        $finalResponse = new LLMResponse(
          new Message(MessageRole::AI, $message->content),
          $usage,
          $dataObject
        );
      }

      $firstCall = false;
    } while ($finalResponse === null);

    return $finalResponse;
  }

  /**
   * Performs REST request and deserializes response
   *
   * @param string $method
   * @param string $resource
   * @param string $responseType
   * @param object|null $payload
   * @return object|null
   * @throws LLMException
   */
  private function doRequest(
    string $method,
    string $resource,
    string $responseType,
    ?object $payload = null
  ): object|null {
    try {
      $options = [
        'headers' => [
          'Authorization' => 'Bearer '.$this->apiKey
        ],
        'timeout' => $this->timeout
      ];

      if ($payload !== null) {
        try {
          $options['json'] = $this->serializer->normalize($payload, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true
          ]);
        } catch (SerializerExceptionInterface $ex) {
          throw new LLMException(
            'Error while normalizing payload.',
            previous: $ex
          );
        }
      }

      $res = $this->httpClient->request(
        $method,
        self::ENDPOINT . $resource,
        $options
      );

      if (($statusCode = $res->getStatusCode()) > 399) {
        throw new LLMException(sprintf(
          'Unexpected answer from Mistral AI backend: [%d] %s',
          $statusCode,
          $res->getContent(false)
        ));
      }

      try {
        return $this->serializer->deserialize($res->getContent(), $responseType, 'json');
      } catch (SerializerExceptionInterface $ex) {
        throw new LLMException(
          'Error while deserializing Mistral AI response: ' . $res->getContent(),
          previous: $ex
        );
      }
    } catch (HttpClientExceptionInterface $ex) {
      throw new LLMException(
        'Error sending request to Mistral AI backend (' . $ex->getMessage() . ')',
        previous: $ex
      );
    }
  }

}
