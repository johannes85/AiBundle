<?php

namespace AiBundle\MCP\Server\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class MCPTool {

  public function __construct(
    public ?string $name = null,
    public ?string $description = null
  ) {}

}
