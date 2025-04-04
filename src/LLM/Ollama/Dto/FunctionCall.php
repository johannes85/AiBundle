<?php

namespace AiBundle\LLM\Ollama\Dto;

class FunctionCall {

  /**
   * @param string $name
   * @param array<mixed> $arguments
   */
  public function __construct(
    public readonly string $name,
    public readonly array $arguments
  ) {}

}
