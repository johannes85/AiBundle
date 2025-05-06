<?php

namespace AiBundle\MCP\Dto;

readonly class ToolDefinition {

  /**
   * @param string $name
   * @param string $description
   * @param array<mixed> $inputSchema
   */
  public function __construct(
    public string $name,
    public string $description,
    public array $inputSchema
  ) {}

}
