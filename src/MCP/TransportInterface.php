<?php

namespace AiBundle\MCP;

use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\JsonRpcResponse;

interface TransportInterface {

  public function connect(): void;

  public function isConnected(): bool;

  public function executeRequest(JsonRpcRequest $request): ?JsonRpcResponse;

  public function disconnect(): void;

}
