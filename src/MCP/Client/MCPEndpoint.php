<?php

namespace AiBundle\MCP\Client;

use AiBundle\MCP\Client\Transport\TransportInterface;
use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\ToolDefinition;
use AiBundle\MCP\Dto\ToolResponse;
use AiBundle\MCP\Dto\ToolsList;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Serializer;

class MCPEndpoint {

  public function __construct(
    private TransportInterface $transport,
    #[Autowire('@ai_bundle.serializer')] private Serializer $serializer
  ) { }

  /**
   * Returns list of tools supported by the server
   *
   * @return array<MCPTool>
   * @throws MCPException
   */
  public function getTools(): array {
    $toolDefinitions = $this->executeRequest(new JsonRpcRequest(
      'tools/list'
    ), ToolsList::class)->tools;
    return array_map(
      fn (ToolDefinition $toolDefinition) => MCPTool::fromToolDefinition($toolDefinition, $this),
      $toolDefinitions
    );
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
    $res = $this->transport->executeRequest($request);
    try {
      return $this->serializer->denormalize($res->result, $responseDataType);
    } catch (SerializerExceptionInterface $ex) {
      throw new MCPException('Failed to denormalize response result object', previous: $ex);
    }
  }

}
