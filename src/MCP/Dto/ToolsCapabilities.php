<?php

namespace AiBundle\MCP\Dto;

readonly class ToolsCapabilities {

  public function __construct(
    public bool $listChanges
  ) { }

}
