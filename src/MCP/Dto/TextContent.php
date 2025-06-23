<?php

namespace AiBundle\MCP\Dto;

readonly class TextContent extends Content {

  public function __construct(
    public string $text
  ) {
    parent::__construct('text');
  }

  public static function buildFrom(mixed $value): self {
    if (is_array($value) || is_object($value)) {
      $value = json_encode($value);
    }
    return new self((string) $value);
  }

}
