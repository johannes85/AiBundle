<?php

namespace AiBundle\MCP\Model;

class ToolResponse {

  public function __construct(
    public array $content,
    public bool $isError = false
  ) {}

}
