<?php

namespace AiBundle\LLM\DeepSeek;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\DeepSeek\Dto\ChatCompletionRequest;
use AiBundle\LLM\DeepSeek\Dto\ChatCompletionResponse;
use AiBundle\LLM\DeepSeek\Dto\DeepSeekFunction;
use AiBundle\LLM\DeepSeek\Dto\DeepSeekMessage;
use AiBundle\LLM\DeepSeek\Dto\DeepSeekTool;
use AiBundle\LLM\DeepSeek\Dto\DeepSeekToolChoice;
use AiBundle\LLM\DeepSeek\Dto\FunctionName;
use AiBundle\LLM\DeepSeek\Dto\ToolCall;
use AiBundle\LLM\DeepSeek\Dto\ToolChoiceType;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMCapabilityException;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\LLMUsage;
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

class DeepSeek extends AbstractLLM {

  private const ENDPOINT = 'https://api.deepseek.com/';

  private const RETURN_DATA_TOOL_NAME = 'return-json-data';

  public function __construct(
    #[SensitiveParameter] private readonly string $apiKey,
    private readonly string $model,
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
    if ($responseDataType !== null && $toolbox !== null) {
      throw new LLMCapabilityException(
        'Structured responses (responseDataType) and tool calling can\'t be used at the same time by this LLM'
      );
    }

    try {
      $format = $responseDataType ? $this->schemaGenerator->generateForClass($responseDataType) : null;
    } catch (SchemaGeneratorException $ex) {
      throw new LLMException(
        'Error generating schema for datatype: ' . $ex->getMessage(),
        previous: $ex
      );
    }

    $deepSeekMessages = array_map(fn (Message $message) => DeepSeekMessage::fromMessage($message), $messages);
    $usage = LLMUsage::empty();

    $finalResponse = null;
    $firstCall = true;
    do {
      $toolbox?->ensureMaxLLMCalls($usage->llmCalls + 1);

      $req =  ChatCompletionRequest::fromGenerateOptions(
        $this->model,
        $deepSeekMessages,
        $options
      );
      if ($toolbox !== null) {
        $req
          ->setTools(array_map(fn (Tool $tool) => new DeepSeekTool(
            DeepSeekFunction::fromTool($tool, $this->toolsHelper)
          ), $toolbox->getTools()))
          ->setToolChoice(
            $firstCall
              ? DeepSeekToolChoice::forToolbox($toolbox)
              : null
          );
      } elseif ($format !== null) {
        $req
          ->setTools([new DeepSeekTool(new DeepSeekFunction(
            self::RETURN_DATA_TOOL_NAME,
            'Returns answer as json data',
            $format
          ))])
          ->setToolChoice(new DeepSeekToolChoice(
            ToolChoiceType::FUNCTION,
            new FunctionName(self::RETURN_DATA_TOOL_NAME)
          ));
      }

      /** @var ChatCompletionResponse $res */
      $res = $this->doRequest(
        'POST',
        '/chat/completions',
        ChatCompletionResponse::class,
        $req
      );
      $usage = $usage->add($res->usage->toLLMUsage());
      $message = $res->choices[0]->message;

      $toolCalls = array_filter(
        $message->toolCalls ?? [],
        fn (ToolCall $toolCall) => $toolCall->function->name !== self::RETURN_DATA_TOOL_NAME
      );
      if (!empty($toolCalls)) {
        $deepSeekMessages[] = $message;
        foreach ($toolCalls as $toolCall) {
          $tool = $toolbox->getTool($toolCall->function->name);
          $toolRes = $this->toolsHelper->callTool($tool, $toolCall->function->arguments);
          $deepSeekMessages[] = new DeepSeekMessage(
            'tool',
            $toolRes,
            name: $tool->name,
            toolCallId: $toolCall->id,
          );
        }
      } else {
        $returnDataToolCalls = array_filter(
          $message->toolCalls ?? [],
          fn (ToolCall $toolCall) => $toolCall->function->name === self::RETURN_DATA_TOOL_NAME
        );
        if (!empty($returnDataToolCalls)) {
          try {
            $dataObject = $format !== null
              ? $this->serializer->deserialize(
                $returnDataToolCalls[0]->function->arguments, $responseDataType, 'json'
              )
              : null;
          } catch (SerializerExceptionInterface) {
            $dataObject = null;
          }

          $finalResponse = new LLMResponse(
            new Message(MessageRole::AI, ''),
            $usage,
            $dataObject
          );
        } else {
          $finalResponse = new LLMResponse(
            new Message(MessageRole::AI, $message->content ?? ''),
            $usage
          );
        }
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
          'Unexpected answer from DeepSeek backend: [%d] %s',
          $statusCode,
          $res->getContent(false)
        ));
      }

      try {
        return $this->serializer->deserialize($res->getContent(), $responseType, 'json');
      } catch (SerializerExceptionInterface $ex) {
        throw new LLMException(
          'Error while deserializing DeepSeek response: ' . $res->getContent(),
          previous: $ex
        );
      }
    } catch (HttpClientExceptionInterface $ex) {
      throw new LLMException(
        'Error sending request to DeepSeek backend (' . $ex->getMessage() . ')',
        previous: $ex
      );
    }
  }

}
