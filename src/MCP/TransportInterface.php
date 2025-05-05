<?php

namespace AiBundle\MCP;

use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\JsonRpcResponse;

interface TransportInterface {

  /**
   * Connects transport
   *
   * @return void
   */
  public function connect(): void;

  /**
   * Returns true if transport is connected
   *
   * @return bool
   */
  public function isConnected(): bool;

  /**
   * Execute a JSON-RPC request and return the response.
   *
   * @param JsonRpcRequest $request
   * @return JsonRpcResponse
   */
  public function executeRequest(JsonRpcRequest $request): JsonRpcResponse;

  /**
   * Disconnects transport
   *
   * @return void
   */
  public function disconnect(): void;

}
