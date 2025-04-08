<?php

namespace AiBundle\LLM\Ollama\Dto;

readonly class OllamaTool {

  public function __construct(
    public OllamaFunction $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
