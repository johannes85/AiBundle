<?php

namespace AiBundle\LLM\Ollama;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMException;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\Ollama\Dto\GenerateChatParameters;
use AiBundle\LLM\Ollama\Dto\OllamaChatResponse;
use AiBundle\LLM\Ollama\Dto\OllamaFunction;
use AiBundle\LLM\Ollama\Dto\OllamaMessage;
use AiBundle\LLM\Ollama\Dto\OllamaOptions;
use AiBundle\LLM\Ollama\Dto\OllamaTool;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolsHelper;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Ollama extends AbstractLLM {

  public function __construct(
    #[SensitiveParameter] private readonly string $endpoint,
    private readonly string $model,
    private readonly float $timeout,
    #[Autowire('@ai_bundle.rest.http_client')] private readonly HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.rest.serializer')] private readonly Serializer $serializer,
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
    try {
      $format = $responseDataType ? $this->schemaGenerator->generateForClass($responseDataType) : null;
    } catch (SchemaGeneratorException $ex) {
      throw new LLMException(
        'Error generating schema for datatype: ' . $ex->getMessage(),
        previous: $ex
      );
    }

    $ollamaMessages = array_map(fn (Message $message) => OllamaMessage::fromMessage($message), $messages);

    $finalResponse = null;
    do {
      /** @var OllamaChatResponse $res */
      $res = $this->doRequest(
        'POST',
        '/api/chat',
        OllamaChatResponse::class,
        (new GenerateChatParameters(
          $this->model,
          $ollamaMessages,
        ))
          ->setStream(false)
          ->setFormat($format)
          ->setOptions($options !== null ? OllamaOptions::fromGenerateOptions($options) : null)
          ->setTools($toolbox !== null
            ? array_map(fn (Tool $tool) => new OllamaTool(
              OllamaFunction::fromTool($tool, $this->toolsHelper)
            ), $toolbox->getTools())
            : null
          )
      );

      $message = $res->message;

      if (!empty($message->toolCalls)) {
        $ollamaMessages[] = $message;
        foreach ($message->toolCalls as $toolCall) {
          $tool = $toolbox->getTool($toolCall->function->name);
          $toolRes = $this->toolsHelper->callTool($tool, $toolCall->function->arguments);
          $ollamaMessages[] = new OllamaMessage(
            'tool',
            $toolRes
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
          $res->message->toMessage(),
          $res->getLLMUsage(),
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
