<?php

namespace AiBundle\LLM\Ollama\Dto;

class ToolCall {

  public function __construct(
    public readonly FunctionCall $function
  ) {}

}
