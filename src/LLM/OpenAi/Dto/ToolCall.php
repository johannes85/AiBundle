<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class ToolCall {

  public function __construct(
    public string $id,
    public FunctionCall $function,
    public string $type = 'function',
  ) {}

}
