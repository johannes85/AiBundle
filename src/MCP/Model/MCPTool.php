<?php

namespace AiBundle\MCP\Model;

readonly class MCPTool {

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
