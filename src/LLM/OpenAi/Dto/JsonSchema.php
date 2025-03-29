<?php

namespace AiBundle\LLM\OpenAi\Dto;

class JsonSchema {

  /**
   * @param string $name
   * @param array<mixed> $schema
   */
  public function __construct(
    public readonly string $name,
    public readonly array $schema
  ) {}

}
