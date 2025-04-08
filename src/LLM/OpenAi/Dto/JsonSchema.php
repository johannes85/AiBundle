<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class JsonSchema {

  /**
   * @param string $name
   * @param array<mixed> $schema
   */
  public function __construct(
    public string $name,
    public array $schema
  ) {}

}
