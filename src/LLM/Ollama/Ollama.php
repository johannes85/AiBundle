<?php

namespace AiBundle\LLM\Ollama;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\LLMDataResponse;
use AiBundle\LLM\LLMException;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\Ollama\Dto\GenerateChatParameters;
use AiBundle\LLM\Ollama\Dto\OllamaMessage;
use AiBundle\LLM\Ollama\Dto\OllamaChatResponse;
use AiBundle\Prompting\Message;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Ollama extends AbstractLLM {

  public function __construct(
    #[SensitiveParameter] private string $endpoint,
    private string $model,
    #[Autowire('@ai_bundle.rest.http_client')] private HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.rest.serializer')] private Serializer $serializer,
    private SchemaGenerator $schemaGenerator
  ) {}

  /**
   * @inheritDoc
   *
   * @param array<Message> $messages
   * @return LLMResponse
   * @throws LLMException
   */
  public function generate(array $messages): LLMResponse {
    /** @var OllamaChatResponse $res */
    $res = $this->doRequest(
      'POST',
      '/api/chat',
      OllamaChatResponse::class,
      (new GenerateChatParameters(
        $this->model,
        array_map(fn (Message $message) => OllamaMessage::fromMessage($message), $messages)
      ))
        ->setStream(false)
    );
    return new LLMResponse(
      $res->getMessage()->toMessage()
    );
  }

  /**
   * @inheritDoc
   *
   * @param array<Message> $messages
   * @param string $datatype
   * @return LLMDataResponse
   * @throws LLMException
   */
  public function generateData(array $messages, string $datatype): LLMDataResponse {
    try {
      $format = $this->schemaGenerator->generateForClass($datatype);
    } catch (SchemaGeneratorException $ex) {
      throw new LLMException(
        'Error generating schema for datatype: ' . $ex->getMessage(),
        previous: $ex
      );
    }

    /** @var OllamaChatResponse $res */
    $res = $this->doRequest(
      'POST',
      '/api/chat',
      OllamaChatResponse::class,
      (new GenerateChatParameters(
        $this->model,
        array_map(fn (Message $message) => OllamaMessage::fromMessage($message), $messages)
      ))
        ->setStream(false)
        ->setFormat($format)
    );
    $message = $res->getMessage();
    try {
      $object = $this->serializer->deserialize($message->getContent(), $datatype, 'json');
    } catch (SerializerExceptionInterface $ex) {
      $object = null;
    }
    return new LLMDataResponse(
      $message->toMessage(),
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
      $options = [];

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
          'Unexpected answer from Ollama backend: [%d] %s',
          $statusCode,
          $res->getContent(false)
        ));
      }

      try {
        return $this->serializer->deserialize($res->getContent(), $responseType, 'json');
      } catch (SerializerExceptionInterface $ex) {
        throw new LLMException(
          'Error while deserializing Ollama response: ' . $res->getContent(),
          previous: $ex
        );
      }
    } catch (HttpClientExceptionInterface $ex) {
      throw new LLMException(
        'Error sending request to Ollama backend (' . $ex->getMessage() . ')', 
        previous: $ex
      );
    }
  }

}
