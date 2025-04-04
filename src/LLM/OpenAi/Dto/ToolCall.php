<?php

namespace AiBundle\LLM\OpenAi\Dto;

class ToolCall {

  public function __construct(
    public readonly string $id,
    public readonly FunctionCall $function,
    public readonly string $type = 'function',
  ) {}

}
