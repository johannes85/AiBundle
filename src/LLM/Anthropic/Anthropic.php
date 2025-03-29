<?php

namespace AiBundle\LLM\Anthropic;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\Anthropic\Dto\AnthropicMessage;
use AiBundle\LLM\Anthropic\Dto\ContentBlock;
use AiBundle\LLM\Anthropic\Dto\MessagesRequest;
use AiBundle\LLM\Anthropic\Dto\MessagesResponse;
use AiBundle\LLM\Anthropic\Dto\Tool;
use AiBundle\LLM\Anthropic\Dto\ToolChoice;
use AiBundle\LLM\Anthropic\Dto\ToolChoiceType;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMDataResponse;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\OpenAi\Dto\AbstractOpenAiMessage;
use AiBundle\LLM\OpenAi\Dto\ChatCompletionRequest;
use AiBundle\LLM\OpenAi\Dto\ChatCompletionResponse;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use AiBundle\LLM\LLMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Anthropic extends AbstractLLM {

  private const ENDPOINT = 'https://api.anthropic.com/v1';
  private const API_VERSION = '2023-06-01';

  private const MAX_TOKENS_DEFAULT = [
    'claude-3-7-sonnet' => 8192,
    'claude-3-5-haiku' => 8192,
    'claude-3-opus' => 4096,
    'claude-3-haiku' => 4096,
    'default' => 8192
  ];

  public function __construct(
    #[SensitiveParameter] private readonly string $apiKey,
    private readonly string $model,
    #[Autowire('@ai_bundle.rest.http_client')] private readonly HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.rest.serializer')] private readonly Serializer $serializer,
    private readonly SchemaGenerator $schemaGenerator,
    private readonly float $timeout = 300
  ) {}

  /**
   * @inheritDoc
   */
  public function generate(
    array $messages,
    ?GenerateOptions $options = null,
    ?string $responseDataType = null
  ): LLMResponse {
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

    $req = MessagesRequest::fromGenerateOptions(
      $this->model,
      $aMessages,
      $this->getModelDefaultMaxTokens($this->model),
      $options
    )
      ->setSystem($systemInstruction);
    if ($format) {
      $req
        ->setTools([
          new Tool(
            'return-json-data',
            'Returns answer as json data',
            $format
          )
        ])
        ->setToolChoice(new ToolChoice(ToolChoiceType::TOOL, name: 'return-json-data'));
    }

    /** @var MessagesResponse $res */
    $res = $this->doRequest(
      'POST',
      '/messages',
      MessagesResponse::class,
      $req
    );

    $content = $res->content[0];
    try {
      $dataObject = $format !== null
        ? $this->serializer->denormalize($content->getInput(), $responseDataType)
        : null;
    } catch (SerializerExceptionInterface) {
      $dataObject = null;
    }
    return new LLMDataResponse(
      new Message(MessageRole::AI, $content->getText() ?? ''),
      $dataObject
    );
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
