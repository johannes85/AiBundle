<?php

namespace AiBundle\LLM\Ollama\Dto;

class OllamaTool {

  public function __construct(
    public readonly OllamaFunction $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
