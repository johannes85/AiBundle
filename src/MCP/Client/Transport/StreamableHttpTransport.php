<?php

namespace AiBundle\MCP\Client\Transport;

use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\JsonRpcResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StreamableHttpTransport implements TransportInterface {

  private const PROTOCOL_VERSION = '2025-06-18';
  private const SUPPORTED_PROTOCOL_VERSIONS = [
    '2025-06-18',
    '2025-03-26'
  ];

  private bool $initialized = false;

  private ?string $neogatedProtocolVersion = null;

  /**
   * @param string $endpoint
   * @param HttpClientInterface $httpClient
   * @param Serializer $serializer
   * @param array<string, string> $headers
   * @param float $timeout
   */
  public function __construct(
    private readonly string $endpoint,
    #[Autowire('@ai_bundle.rest.http_client')] private readonly HttpClientInterface $httpClient,
    #[Autowire('@ai_bundle.serializer')] private readonly Serializer $serializer,
    private readonly array $headers = [],
    private readonly float $timeout = 30
  ) { }

  /**
   * Sends initialization request to the MCP server to check protocol version.
   *
   * @return void
   * @throws MCPTransportException
   */
  private function initialize(): void {
    if (!$this->initialized) {
      $this->initialized = true;
      $res = $this->executeRequest(new JsonRpcRequest(
        'initialize',
        [
          'protocolVersion' => self::PROTOCOL_VERSION,
          'capabilities' => [],
          'clientInfo' => [
            'name' => 'AiBundle',
            'version' => '1.0.0'
          ]
        ]
      ));
      $serverProtocolVersion = $res->result['protocolVersion'] ?? 'undefined';
      if (!in_array($serverProtocolVersion, self::SUPPORTED_PROTOCOL_VERSIONS)) {
        throw new MCPTransportException(sprintf(
          'Unsupported protocol version: %s. Supported versions: %s',
          $serverProtocolVersion,
          implode(', ', self::SUPPORTED_PROTOCOL_VERSIONS)
        ));
      }
      $this->neogatedProtocolVersion = $serverProtocolVersion;
    }
  }

  /**
   * Execute a JSON-RPC request and return the response.
   *
   * @param JsonRpcRequest $request
   * @return JsonRpcResponse
   * @throws MCPTransportException
   */
  public function executeRequest(JsonRpcRequest $request): JsonRpcResponse {
    $this->initialize();

    try {
      $request = clone $request;
      if ($request->id === null) {
        $request = new JsonRpcRequest(
          $request->method,
          $request->params,
          Uuid::v4()->toRfc4122()
        );
      }
      try {
        $options = [
          'headers' => array_merge(
            $this->headers,
            ['MCP-Protocol-Version' => $this->neogatedProtocolVersion]
          ),
          'json' => $this->serializer->normalize($request, null, [
            Serializer::EMPTY_ARRAY_AS_OBJECT => true
          ]),
          'timeout' => $this->timeout
        ];
      } catch (SerializerExceptionInterface $ex) {
        throw new MCPTransportException(
          'Error while normalizing payload.',
          previous: $ex
        );
      }

      $res = $this->httpClient->request(
        'POST',
        $this->endpoint,
        $options
      );
      $resHeaders = $res->getHeaders();
      if ($resHeaders['content-type'][0] !== 'application/json') {
        throw new MCPTransportException(sprintf(
          'Unexpected content type "%s" in response. Expected "application/json". Note: text/event-stream is not supported at the moment.',
          $resHeaders['content-type'][0]
        ));
      }
      try {
        return $this->serializer->deserialize($res->getContent(), JsonRpcResponse::class, 'json');
      } catch (SerializerExceptionInterface $ex) {
        throw new MCPTransportException('Failed to deserialize json rpc response', previous: $ex);
      }
    } catch (HttpClientExceptionInterface $ex) {
      throw new MCPTransportException(
        'Error sending request to MCP server (' . $ex->getMessage() . ')',
        previous: $ex
      );
    }
  }

}
