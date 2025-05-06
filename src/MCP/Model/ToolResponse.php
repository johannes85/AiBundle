<?php

namespace AiBundle\MCP\Model;

class ToolResponse {

  /**
   * @param array<mixed> $content
   * @param bool $isError
   */
  public function __construct(
    public array $content,
    public bool $isError = false
  ) {}

}
