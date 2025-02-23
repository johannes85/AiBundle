<?php

namespace Johannes85\AiBundle\LLM\OpenAi;

use Johannes85\AiBundle\LLM\AbstractLLM;
use Johannes85\AiBundle\LLM\LLMResponse;
use Johannes85\AiBundle\LLM\OpenAi\Dto\AbstractOpenAiMessage;
use Johannes85\AiBundle\LLM\OpenAi\Dto\ChatCompletionRequest;
use Johannes85\AiBundle\LLM\OpenAi\Dto\ChatCompletionResponse;
use Johannes85\AiBundle\Prompting\Message;
use Johannes85\AiBundle\Prompting\MessageRole;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Johannes85\AiBundle\LLM\LLMException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAi extends AbstractLLM {

  private const ENDPOINT = 'https://api.openai.com/v1';

  public function __construct(
    #[SensitiveParameter] private string $apiKey,
    private string $model,
    #[Autowire('@ai_bundle.rest.http_client')] private HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.rest.serializer')] private Serializer $serializer
  ) {}

  /**
   * @inheritDoc
   *
   * @param array $messages
   * @return LLMResponse
   */
  public function generate(array $messages): LLMResponse {
    /** @var ChatCompletionResponse $res */
    $res = $this->doRequest(
      'POST',
      '/chat/completions',
      ChatCompletionResponse::class,
      (new ChatCompletionRequest(
        $this->model,
        array_map(fn (Message $message) => AbstractOpenAiMessage::fromMessage($message), $messages),
      ))
    );
    return new LLMResponse(
      new Message(MessageRole::AI, $res->getChoices()[0]->getMessage()->getContent())
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
        ]
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
