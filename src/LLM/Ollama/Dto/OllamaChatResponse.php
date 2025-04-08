<?php

namespace AiBundle\LLM\Ollama\Dto;

readonly class OllamaChatResponse {

  public function __construct(
    public OllamaMessage $message
  ) {}

}
