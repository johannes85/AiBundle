<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class FunctionCall {

  public function __construct(
    public string $name,
    public string $arguments
  ) {}

}
