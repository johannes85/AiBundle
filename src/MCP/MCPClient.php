<?php

namespace AiBundle\MCP;

use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\ToolsList;
use AiBundle\MCP\Model\ToolResponse;
use AiBundle\MCP\Model\MCPTool;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

class MCPClient {

  public function __construct(
    private TransportInterface $transport,
    private Serializer $serializer
  ) { }

  /**
   * Returns list of tools supported by the server
   *
   * @return array<MCPTool>
   * @throws MCPException
   */
  public function getTools(): array {
    return $this->executeRequest(new JsonRpcRequest(
      'tools/list'
    ), ToolsList::class)->tools;
  }

  /**
   * Calls tool
   *
   * @param string $name
   * @param array<mixed> $arguments
   * @return ToolResponse
   * @throws MCPException
   */
  public function callTool(string $name, array $arguments = []): ToolResponse {
    $request = new JsonRpcRequest(
      'tools/call',
      [
        'name' => $name,
        'arguments' => $arguments
      ]
    );
    return $this->executeRequest($request, ToolResponse::class);
  }

  /**
   * Executes json rpc request via transport instance
   *
   * @param JsonRpcRequest $request
   * @param string $responseDataType
   * @return object
   * @throws MCPException
   */
  private function executeRequest(JsonRpcRequest $request, string $responseDataType): object {
    if (!$this->transport->isConnected()) {
      $this->transport->connect();
    }
    $res = $this->transport->executeRequest($request);
    try {
      return $this->serializer->denormalize($res->result, $responseDataType);
    } catch (SerializerExceptionInterface $ex) {
      throw new MCPException('Failed to denormalize response result object', previous: $ex);
    }
  }

}
