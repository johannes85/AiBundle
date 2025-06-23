<?php

namespace AiBundle\MCP\Dto;

class JsonRpcError {

  /**
   * @param int $code
   * @param string $message
   * @param array<mixed>|null|string $data
   */
  public function __construct(
    public int $code,
    public string $message,
    public null|array|string $data = null
  ) {}

}
