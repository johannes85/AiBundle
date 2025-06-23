<?php

namespace AiBundle\MCP\Dto;

readonly class Capabilities {

  public function __construct(
    public ToolsCapabilities $toolsCapabilities
  ) {}

}
