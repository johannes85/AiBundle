<?php

namespace AiBundle\MCP\Dto;

readonly class TextContent extends Content {

  public function __construct(
    public string $text
  ) {
    parent::__construct('text');
  }

}
