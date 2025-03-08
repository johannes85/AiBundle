<?php

namespace Johannes85\AiBundle\LLM\Ollama;

use Johannes85\AiBundle\LLM\AbstractLLM;
use Johannes85\AiBundle\LLM\LLMException;
use Johannes85\AiBundle\LLM\LLMResponse;
use Johannes85\AiBundle\LLM\Ollama\Dto\GenerateChatParameters;
use Johannes85\AiBundle\LLM\Ollama\Dto\OllamaMessage;
use Johannes85\AiBundle\LLM\Ollama\Dto\OllamaChatResponse;
use Johannes85\AiBundle\Prompting\Message;
use Johannes85\AiBundle\Prompting\MessageRole;
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
    #[Autowire('@ai_bundle.rest.serializer')] private Serializer $serializer
  ) {}

  /**
   * @inheritDoc
   *
   * @param Message[] $messages
   * @return LLMResponse
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
