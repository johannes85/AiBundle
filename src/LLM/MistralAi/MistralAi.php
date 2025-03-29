<?php

namespace AiBundle\LLM\MistralAi;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMDataResponse;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\MistralAi\Dto\ChatCompletionRequest;
use AiBundle\LLM\MistralAi\Dto\ChatCompletionResponse;
use AiBundle\LLM\MistralAi\Dto\JsonSchema;
use AiBundle\LLM\MistralAi\Dto\ResponseFormat;
use AiBundle\LLM\MistralAi\Dto\MistralAiMessage;
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

class MistralAi extends AbstractLLM {

  private const ENDPOINT = 'https://api.mistral.ai/v1';

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

    /** @var ChatCompletionResponse $res */
    $res = $this->doRequest(
      'POST',
      '/chat/completions',
      ChatCompletionResponse::class,
      ChatCompletionRequest::fromGenerateOptions(
        $this->model,
        array_map(fn (Message $message) => MistralAiMessage::fromMessage($message), $messages),
        $options
      )
        ->setResponseFormat($format !== null ? new ResponseFormat(
          'json_schema',
          new JsonSchema('response', $format)
        ) : null)
    );

    $message = $res->choices[0]->message;
    try {
      $dataObject = $format !== null
        ? $this->serializer->deserialize($message->content, $responseDataType, 'json')
        : null;
    } catch (SerializerExceptionInterface) {
      $dataObject = null;
    }

    return new LLMResponse(
      new Message(MessageRole::AI, $message->content),
      $dataObject
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
        array_map(fn (Message $message) => MistralAiMessage::fromMessage($message), $messages),
        $options
      )
        ->setResponseFormat(new ResponseFormat(
          'json_schema',
          new JsonSchema('response', $format)
        ))
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
