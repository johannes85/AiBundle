<?php

namespace AiBundle\LLM\GoogleAi;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\GoogleAi\Dto\Content;
use AiBundle\LLM\GoogleAi\Dto\GenerateContentParameters;
use AiBundle\LLM\GoogleAi\Dto\GenerationConfig;
use AiBundle\LLM\GoogleAi\Dto\Part;
use AiBundle\LLM\LLMDataResponse;
use AiBundle\LLM\LLMResponse;
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
use AiBundle\LLM\GoogleAi\Dto\GoogleAiResponse;

class GoogleAi extends AbstractLLM {

  private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta';

  public const MESSAGE_ROLE_MAPPING = [
    MessageRole::AI->name => 'model',
    MessageRole::HUMAN->name => 'user',
    MessageRole::SYSTEM->name => 'system',
  ];

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
    $res = $this->abstractGenerate(
      $messages,
      $options ? GenerationConfig::fromGenerateOptions($options) : null
    );
    return new LLMResponse(
      new Message(MessageRole::AI, $res->candidates[0]->content->parts[0]->getText())
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

    $res = $this->abstractGenerate(
      $messages,
      GenerationConfig::fromGenerateOptions($options)
        ->setResponseMimeType('application/json')
        ->setResponseSchema($format)
    );
    $responseText = $res->candidates[0]->content->parts[0]->getText();
    try {
      $object = $this->serializer->deserialize($responseText, $datatype, 'json');
    } catch (SerializerExceptionInterface $ex) {
      $object = null;
    }
    return new LLMDataResponse(
      new Message(MessageRole::AI, $responseText),
      $object
    );
  }

  /**
   * Generates content
   *
   * @param array $messages
   * @param GenerationConfig $generationConfig
   * @return GoogleAiResponse
   * @throws LLMException
   */
  private function abstractGenerate(
    array $messages,
    GenerationConfig $generationConfig
  ): GoogleAiResponse {
    $systemInstruction = null;
    if (!empty($messages) && $messages[0]->role === MessageRole::SYSTEM) {
      $systemInstruction = new Content([(new Part())->setText($messages[0]->content)]);
      array_shift($messages);
    }
    $contents = [];
    foreach ($messages as $message) {
      $contents[] = new Content(
        [(new Part())->setText($message->content)],
        self::MESSAGE_ROLE_MAPPING[$message->role->name]
      );
    }

    return $this->doRequest(
      'POST',
      'generateContent',
      GoogleAiResponse::class,
      new GenerateContentParameters(
        $contents,
        $systemInstruction,
        $generationConfig
      )
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
      $url = self::ENDPOINT.'/models/'.urlencode($this->model).':'.$resource;

      $options = [
        'query' => ['key' => $this->apiKey],
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
        $url,
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
