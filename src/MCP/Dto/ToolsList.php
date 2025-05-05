<?php

namespace AiBundle\MCP\Dto;

use AiBundle\MCP\Model\MCPTool;

readonly class ToolsList {

  /**
   * @param array<MCPTool> $tools
   */
  public function __construct(
    public array $tools
  ) {}

}
