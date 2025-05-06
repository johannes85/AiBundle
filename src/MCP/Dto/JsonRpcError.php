<?php

namespace AiBundle\MCP\Dto;

class JsonRpcError {

  /**
   * @param int $code
   * @param string $message
   * @param array<mixed>|null $data
   */
  public function __construct(
    public int $code,
    public string $message,
    public null|array $data = null
  ) {}

}
