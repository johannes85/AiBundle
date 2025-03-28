<?php

namespace AiBundle\LLM\MistralAi\Dto;

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
