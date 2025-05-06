<?php

namespace AiBundle\MCP\Dto;
readonly class JsonRpcRequest {

  private const DEFAULT_JSON_RPC_VERSION = '2.0';

  /**
   * @param string $method
   * @param array<mixed> $params
   * @param string|int|null $id
   * @param string $jsonrpc
   */
  public function __construct(
    public string $method,
    public array $params = [],
    public null|string|int $id = null,
    public string $jsonrpc = self::DEFAULT_JSON_RPC_VERSION
  ) {}

}
