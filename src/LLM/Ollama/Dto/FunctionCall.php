<?php

namespace AiBundle\LLM\Ollama\Dto;

readonly class FunctionCall {

  /**
   * @param string $name
   * @param array<mixed> $arguments
   */
  public function __construct(
    public string $name,
    public array $arguments
  ) {}

}
