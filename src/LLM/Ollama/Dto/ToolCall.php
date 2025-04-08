<?php

namespace AiBundle\LLM\Ollama\Dto;

readonly class ToolCall {

  public function __construct(
    public FunctionCall $function
  ) {}

}
