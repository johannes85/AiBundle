<?php

namespace AiBundle\MCP\Server;

use RuntimeException;

class MCPToolError extends RuntimeException {

  public function __construct(
    public string $content
  ) {
    parent::__construct();
  }

}
