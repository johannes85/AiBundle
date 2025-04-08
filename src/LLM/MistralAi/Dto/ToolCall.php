<?php

namespace AiBundle\LLM\MistralAi\Dto;

readonly class ToolCall {

  public function __construct(
    public string $id,
    public FunctionCall $function,
    public string $type = 'function',
  ) {}

}
