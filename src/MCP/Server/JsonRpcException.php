<?php

namespace AiBundle\MCP\Server;

use AiBundle\MCP\Dto\JsonRpcError;
use AiBundle\MCP\Dto\JsonRpcResponse;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Throwable;

class JsonRpcException extends RuntimeException {

  public const CODE_PARSE_ERROR = -32700;
  public const CODE_INVALID_REQUEST = -32600;
  public const CODE_METHOD_NOT_FOUND = -32601;
  public const CODE_INVALID_PARAMS = -32602;
  public const CODE_INTERNAL_ERROR = -32603;

  private const CODE_MESSAGE_MAPPING = [
    self::CODE_PARSE_ERROR => 'Parse error',
    self::CODE_INVALID_REQUEST => 'Invalid Request',
    self::CODE_METHOD_NOT_FOUND => 'Method not found',
    self::CODE_INVALID_PARAMS => 'Invalid params',
    self::CODE_INTERNAL_ERROR => 'Internal error',
  ];

  /**
   * @param ?string $message
   * @param int $code
   * @param null|string|array<mixed> $data
   * @param Throwable|null $previous
   */
  public function __construct(
    int $code = self::CODE_INTERNAL_ERROR,
    ?string $message = null,
    private null|string|array $data = null,
    ?Throwable $previous = null
  ) {
    parent::__construct(
      $message ?? self::CODE_MESSAGE_MAPPING[$code] ?? 'Unknown, error',
      $code,
      $previous
    );
  }

  /**
   * Generates a JSON-RPC response from this exception
   *
   * @param string|null $withId
   * @return JsonRpcResponse
   */
  public function toJsonRpcResponse(?string $withId = null): JsonRpcResponse {
    return new JsonRpcResponse(
      jsonrpc: '2.0',
      error: new JsonRpcError(
        code: $this->getCode(),
        message: $this->getMessage(),
        data: $this->data
      ),
      id: $withId
    );
  }

}
