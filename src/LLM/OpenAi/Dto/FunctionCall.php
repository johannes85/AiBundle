<?php

namespace AiBundle\LLM\OpenAi\Dto;

class FunctionCall {

  public function __construct(
    public readonly string $name,
    public readonly string $arguments
  ) {}

}
