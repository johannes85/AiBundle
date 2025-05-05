<?php

namespace AiBundle\MCP\Dto;

class JsonRpcError {

  public function __construct(
    public int $code,
    public string $message,
    public null|array $data = null
  ) {}

}
