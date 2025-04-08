<?php

namespace AiBundle\LLM\MistralAi\Dto;

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
