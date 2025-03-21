<?php

namespace AiBundle\LLM\OpenAi;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
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

class OpenAi extends AbstractLLM {

  private const ENDPOINT = 'https://api.openai.com/v1';

  public function __construct(
    #[SensitiveParameter] private readonly string $apiKey,
    private readonly string $model,
    #[Autowire('@ai_bundle.rest.http_client')] private readonly HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.rest.serializer')] private readonly Serializer $serializer,
    private readonly SchemaGenerator $schemaGenerator,
    private float $idleTimeout = 300
  ) {}

  /**
   * @inheritDoc
   *
   * @param array<Message> $messages
   * @param GenerateOptions|null $options
   * @return LLMResponse
   * @throws LLMException
   */
  public function generate(array $messages, ?GenerateOptions $options = null): LLMResponse {
    /** @var ChatCompletionResponse $res */
    $res = $this->doRequest(
      'POST',
      '/chat/completions',
      ChatCompletionResponse::class,
      ChatCompletionRequest::fromGenerateOptions(
        $this->model,
        array_map(fn (Message $message) => AbstractOpenAiMessage::fromMessage($message), $messages),
        $options
      )
    );
    return new LLMResponse(
      new Message(MessageRole::AI, $res->choices[0]->message->content)
    );
  }

  /**
   * @inheritDoc
   *
   * @param array<Message> $messages
   * @param string $datatype
   * @param GenerateOptions|null $options
   * @return LLMDataResponse
   * @throws LLMException
   */
  public function generateData(array $messages, string $datatype, ?GenerateOptions $options = null): LLMDataResponse {
    try {
      $format = $this->schemaGenerator->generateForClass($datatype);
    } catch (SchemaGeneratorException $ex) {
      throw new LLMException(
        'Error generating schema for datatype: ' . $ex->getMessage(),
        previous: $ex
      );
    }

    /** @var ChatCompletionResponse $res */
    $res = $this->doRequest(
      'POST',
      '/chat/completions',
      ChatCompletionResponse::class,
      ChatCompletionRequest::fromGenerateOptions(
        $this->model,
        array_map(fn (Message $message) => AbstractOpenAiMessage::fromMessage($message), $messages),
        $options
      )
        ->setResponseFormat([
          'type' => 'json_schema',
          'json_schema' => [
            'name' => 'response',
            'schema' => $format
          ]
        ])
    );
    $message = $res->choices[0]->message;
    try {
      $object = $this->serializer->deserialize($message->content, $datatype, 'json');
    } catch (SerializerExceptionInterface $ex) {
      $object = null;
    }
    return new LLMDataResponse(
      new Message(MessageRole::AI, $message->content),
      $object
    );
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
        'timeout' => $this->idleTimeout
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
