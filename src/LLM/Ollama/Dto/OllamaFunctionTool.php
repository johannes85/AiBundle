<?php

namespace AiBundle\LLM\Ollama\Dto;

class OllamaFunctionTool {

  public function __construct(
    public readonly array $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
