<?php

namespace AiBundle\MCP\Client\Transport;

use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\JsonRpcResponse;

interface TransportInterface {

  /**
   * Execute a JSON-RPC request and return the response.
   *
   * @param JsonRpcRequest $request
   * @return JsonRpcResponse
   */
  public function executeRequest(JsonRpcRequest $request): JsonRpcResponse;

}
