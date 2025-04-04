<?php

namespace AiBundle\LLM\OpenAi;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\OpenAi\Dto\ChatCompletionRequest;
use AiBundle\LLM\OpenAi\Dto\ChatCompletionResponse;
use AiBundle\LLM\OpenAi\Dto\JsonSchema;
use AiBundle\LLM\OpenAi\Dto\OpenAiFunction;
use AiBundle\LLM\OpenAi\Dto\OpenAiMessage;
use AiBundle\LLM\OpenAi\Dto\OpenAiTool;
use AiBundle\LLM\OpenAi\Dto\ResponseFormat;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Prompting\Tools\ToolsHelperException;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use AiBundle\LLM\LLMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAi extends AbstractLLM {

  public function __construct(
    #[SensitiveParameter] private readonly string $apiKey,
    private readonly string $model,
    private readonly string $endpoint,
    private readonly float $timeout,
    #[Autowire('@ai_bundle.rest.http_client')] private readonly HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.rest.serializer')] private readonly Serializer $serializer,
    private readonly SchemaGenerator $schemaGenerator,
    private readonly ToolsHelper $toolsHelper,
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

    $openAiMessages = array_map(fn (Message $message) => OpenAiMessage::fromMessage($message), $messages);

    $finalResponse = null;
    do {
      /** @var ChatCompletionResponse $res */
      $res = $this->doRequest(
        'POST',
        '/chat/completions',
        ChatCompletionResponse::class,
        ChatCompletionRequest::fromGenerateOptions(
          $this->model,
          $openAiMessages,
          $options
        )
          ->setResponseFormat($format !== null ? new ResponseFormat(
            'json_schema',
            new JsonSchema('response', $format)
          ) : null)
          ->setTools($toolbox !== null
            ? array_map(fn (Tool $tool) => new OpenAiTool(
              OpenAiFunction::fromTool($tool, $this->toolsHelper)
            ), $toolbox->getTools())
            : null
          )
      );

      $message = $res->choices[0]->message;

      if (!empty($message->toolCalls)) {
        $openAiMessages[] = $message;
        foreach ($message->toolCalls as $toolCall) {
          $tool = $toolbox->getTool($toolCall->function->name);
          $res = $this->toolsHelper->callTool($tool, $toolCall->function->arguments);

          $openAiMessages[] = new OpenAiMessage(
            'tool',
            $res,
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
          $dataObject
        );
      }
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
        $this->endpoint . $resource,
        $options
      );

      if (($statusCode = $res->getStatusCode()) > 399) {
        throw new LLMException(sprintf(
          'Unexpected answer from OpenAi backend: [%d] %s',
          $statusCode,
          $res->getContent(false)
        ));
      }

      try {
        return $this->serializer->deserialize($res->getContent(), $responseType, 'json');
      } catch (SerializerExceptionInterface $ex) {
        throw new LLMException(
          'Error while deserializing OpenAi response: ' . $res->getContent(),
          previous: $ex
        );
      }
    } catch (HttpClientExceptionInterface $ex) {
      throw new LLMException(
        'Error sending request to OpenAi backend (' . $ex->getMessage() . ')',
        previous: $ex
      );
    }
  }

}
