<?php

namespace AiBundle\MCP\Dto;
readonly class JsonRpcResponse {

  public function __construct(
    public string $jsonrpc,
    public mixed $result = null,
    public ?JsonRpcError $error = null,
    public null|string|int $id = null
  ) {}

}
