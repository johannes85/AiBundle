<?php

namespace AiBundle\LLM\Anthropic;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\Anthropic\Dto\AnthropicMessage;
use AiBundle\LLM\Anthropic\Dto\AnthropicTool;
use AiBundle\LLM\Anthropic\Dto\ContentBlock;
use AiBundle\LLM\Anthropic\Dto\ContentBlockType;
use AiBundle\LLM\Anthropic\Dto\MessagesRequest;
use AiBundle\LLM\Anthropic\Dto\MessagesResponse;
use AiBundle\LLM\Anthropic\Dto\AnthropicToolChoice;
use AiBundle\LLM\Anthropic\Dto\ToolChoiceType;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMCapabilityException;
use AiBundle\LLM\LLMException;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\LLMUsage;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\AbstractTool;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Serializer\EmptyObjectHelper;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Anthropic extends AbstractLLM {

  private const ENDPOINT = 'https://api.anthropic.com/v1';
  private const API_VERSION = '2023-06-01';

  private const RETURN_DATA_TOOL_NAME = 'return-json-data';

  private const MAX_TOKENS_DEFAULT = [
    'claude-opus-4-0' => 8192,
    'claude-sonnet-4-0' => 8192,
    'claude-3-7-sonnet' => 8192,
    'claude-3-5-haiku' => 8192,
    'claude-3-opus' => 4096,
    'claude-3-haiku' => 4096,
    'default' => 8192
  ];

  public function __construct(
    #[SensitiveParameter] private readonly string $apiKey,
    private readonly string $model,
    private readonly float $timeout,
    #[Autowire('@ai_bundle.rest.http_client')] private readonly HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.serializer')] private readonly Serializer $serializer,
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

    $systemInstruction = null;
    if (!empty($messages) && $messages[0]->role === MessageRole::SYSTEM) {
      $systemInstruction = $messages[0]->content;
      array_shift($messages);
    }
    $aMessages = array_map(fn (Message $message) => AnthropicMessage::fromMessage($message), $messages);
    $usage = LLMUsage::empty();

    $finalResponse = null;
    $firstCall = true;
    do {
      $toolbox?->ensureMaxLLMCalls($usage->llmCalls + 1);

      $req = MessagesRequest::fromGenerateOptions(
        $this->model,
        $aMessages,
        $this->getModelDefaultMaxTokens($this->model),
        $options
      )
        ->setSystem($systemInstruction);
      if ($toolbox !== null) {
        $req
          ->setTools(array_map(fn (AbstractTool $tool) => new AnthropicTool(
            $tool->name,
            $tool->description,
            EmptyObjectHelper::injectEmptyObjects($this->toolsHelper->getToolCallbackSchema($tool))
          ), $toolbox->getTools()))
          ->setToolChoice($firstCall ? AnthropicToolChoice::forToolbox($toolbox) : null);
      } elseif ($format !== null) {
        $req
          ->setTools([new AnthropicTool(
            self::RETURN_DATA_TOOL_NAME,
            'Returns answer as json data',
            $format
          )])
          ->setToolChoice(new AnthropicToolChoice(ToolChoiceType::TOOL, name: self::RETURN_DATA_TOOL_NAME));
      }

      /** @var MessagesResponse $res */
      $res = $this->doRequest(
        'POST',
        '/messages',
        MessagesResponse::class,
        $req
      );
      $usage = $usage->add($res->usage->toLLMUsage());

      $toolCalls = array_filter(
        $res->content,
        fn (ContentBlock $contentBlock) =>
          $contentBlock->getType() === ContentBlockType::TOOL_USE &&
          $contentBlock->getName() !== self::RETURN_DATA_TOOL_NAME
      );
      if (!empty($toolCalls)) {
        $aMessages[] = new AnthropicMessage(
          'assistant',
          $res->content
        );
        foreach ($toolCalls as $toolCall) {
          $tool = $toolbox->getTool($toolCall->getName());
          $res = $this->toolsHelper->callTool($tool, $toolCall->getInput());
          $aMessages[] = new AnthropicMessage(
            'user',
            [
              (new ContentBlock())
                ->setType(ContentBlockType::TOOL_RESULT)
                ->setToolUseId($toolCall->getId())
                ->setContent($res)
            ]
          );
        }
      } else {
        $returnDataToolCalls = array_filter(
          $res->content,
          fn (ContentBlock $contentBlock) =>
            $contentBlock->getType() === ContentBlockType::TOOL_USE &&
            $contentBlock->getName() === self::RETURN_DATA_TOOL_NAME
        );
        if (!empty($returnDataToolCalls)) {
          try {
            $dataObject = $format !== null
              ? $this->serializer->denormalize($returnDataToolCalls[0]->getInput(), $responseDataType)
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
            new Message(
            MessageRole::AI,
            count($res->content) > 0 ? ($res->content[0]->getText() ?? '') : ''
            ),
            $usage
          );
        }
      }

      $firstCall = false;
    } while ($finalResponse === null);

    return $finalResponse;
  }

  /**
   * Returns default max tokens for given model
   *
   * @param string $model
   * @return int
   */
  private function getModelDefaultMaxTokens(string $model): int {
    foreach (self::MAX_TOKENS_DEFAULT as $key => $value) {
      if (str_starts_with($model, $key)) {
        return $value;
      }
    }
    return self::MAX_TOKENS_DEFAULT['default'];
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
          'x-api-key' => $this->apiKey,
          'anthropic-version' => self::API_VERSION
        ],
        'timeout' => $this->timeout
      ];

      if ($payload !== null) {
        try {
          $options['json'] = $this->serializer->normalize($payload, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true
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
          'Unexpected answer from Anthropic backend: [%d] %s',
          $statusCode,
          $res->getContent(false)
        ));
      }

      try {
        return $this->serializer->deserialize($res->getContent(), $responseType, 'json');
      } catch (SerializerExceptionInterface $ex) {
        throw new LLMException(
          'Error while deserializing Anthropic response: ' . $res->getContent(),
          previous: $ex
        );
      }
    } catch (HttpClientExceptionInterface $ex) {
      throw new LLMException(
        'Error sending request to Anthropic backend (' . $ex->getMessage() . ')',
        previous: $ex
      );
    }
  }

}
