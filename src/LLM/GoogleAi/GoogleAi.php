<?php

namespace AiBundle\LLM\GoogleAi;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\LLM\AbstractLLM;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\GoogleAi\Dto\Content;
use AiBundle\LLM\GoogleAi\Dto\FunctionCallingConfig;
use AiBundle\LLM\GoogleAi\Dto\FunctionDeclaration;
use AiBundle\LLM\GoogleAi\Dto\FunctionResponse;
use AiBundle\LLM\GoogleAi\Dto\GenerateContentParameters;
use AiBundle\LLM\GoogleAi\Dto\GenerationConfig;
use AiBundle\LLM\GoogleAi\Dto\GoogleAiTool;
use AiBundle\LLM\GoogleAi\Dto\InlineData;
use AiBundle\LLM\GoogleAi\Dto\Part;
use AiBundle\LLM\GoogleAi\Dto\ToolConfig;
use AiBundle\LLM\LLMCapabilityException;
use AiBundle\LLM\LLMResponse;
use AiBundle\LLM\LLMUsage;
use AiBundle\Prompting\FileType;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolChoice;
use AiBundle\Prompting\Tools\ToolsHelper;
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

    $systemInstruction = null;
    if (!empty($messages) && $messages[0]->role === MessageRole::SYSTEM) {
      $systemInstruction = new Content([(new Part())->setText($messages[0]->content)]);
      array_shift($messages);
    }
    $contents = [];
    foreach ($messages as $message) {
      $parts = [];
      foreach ($message->files as $file) {
        /** @phpstan-ignore notIdentical.alwaysFalse */
        if ($file->type !== FileType::IMAGE) {
          continue;
        }
        $parts[] = (new Part())->setInlineData(
          new InlineData(
            $file->mimeType,
            $file->getBase64Content()
          )
        );
      }
      $parts[] = (new Part())->setText($message->content);
      $contents[] = new Content(
        $parts,
        self::MESSAGE_ROLE_MAPPING[$message->role->name]
      );
    }

    $generationConfig = null;
    if ($options !== null || $format !== null) {
      $generationConfig = GenerationConfig::fromGenerateOptions($options);
      if ($format !== null) {
        $generationConfig
          ->setResponseMimeType('application/json')
          ->setResponseSchema($format);
      }
    }

    $usage = LLMUsage::empty();

    $finalResponse = null;
    $firstCall = true;
    do {
      $toolbox?->ensureMaxLLMCalls($usage->llmCalls + 1);

      /** @var GoogleAiResponse $res */
      $res = $this->doRequest(
        'POST',
        'generateContent',
        GoogleAiResponse::class,
        (new GenerateContentParameters(
          $contents,
          $systemInstruction,
          $generationConfig
        ))
          ->setTools($toolbox !== null ? array_map(fn (Tool $tool) => new GoogleAiTool(
            [FunctionDeclaration::fromTool($tool, $this->toolsHelper)]
          ), $toolbox->getTools()) : null)
          ->setToolConfig(
            $toolbox !== null && $firstCall
              ? new ToolConfig(FunctionCallingConfig::forToolbox($toolbox))
              : null
          )
      );

      /** @var Content $content */
      $content = $res->candidates[0]->content;
      $usage = $usage->add($res->usageMetadata->toLLMUsage());
      $functionCallParts = array_filter($content->parts, fn(Part $part) => !empty($part->getFunctionCall()));

      if (!empty($functionCallParts)) {
        $contents[] = $content;

        $functionResponseParts = [];
        foreach ($functionCallParts as $part) {
          $functionCall = $part->getFunctionCall();
          $tool = $toolbox->getTool($functionCall->name);
          $toolRes = $this->toolsHelper->callTool($tool, $functionCall->args);
          if (!is_array($toolRes)) {
            $toolRes = ['content' => $toolRes];
          }
          $functionResponseParts[] = (new Part())->setFunctionResponse(new FunctionResponse(
            $functionCall->name,
            $toolRes
          ));
        }
        $contents[] = new Content($functionResponseParts);
      } else {
        $responseText = $content->parts[0]->getText();
        try {
          $dataObject = $format !== null
            ? $this->serializer->deserialize($responseText, $responseDataType, 'json')
            : null;
        } catch (SerializerExceptionInterface) {
          $dataObject = null;
        }

        $finalResponse = new LLMResponse(
          new Message(MessageRole::AI, $responseText),
          $usage,
          $dataObject
        );
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
      $url = self::ENDPOINT.'/models/'.urlencode($this->model).':'.$resource;

      $options = [
        'query' => ['key' => $this->apiKey],
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
        $url,
        $options
      );

      if (($statusCode = $res->getStatusCode()) > 399) {
        throw new LLMException(sprintf(
          'Unexpected answer from GoogleAi backend: [%d] %s',
          $statusCode,
          $res->getContent(false)
        ));
      }

      try {
        return $this->serializer->deserialize($res->getContent(), $responseType, 'json');
      } catch (SerializerExceptionInterface $ex) {
        throw new LLMException(
          'Error while deserializing GoogleAi response: ' . $res->getContent(),
          previous: $ex
        );
      }
    } catch (HttpClientExceptionInterface $ex) {
      throw new LLMException(
        'Error sending request to GoogleAi backend (' . $ex->getMessage() . ')',
        previous: $ex
      );
    }
  }

}
