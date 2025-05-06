<?php

namespace AiBundle\MCP\Dto;

readonly class ToolsList {

  /**
   * @param array<ToolDefinition> $tools
   */
  public function __construct(
    public array $tools
  ) {}

}
