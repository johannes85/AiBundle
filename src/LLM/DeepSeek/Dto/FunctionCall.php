<?php

namespace AiBundle\LLM\DeepSeek\Dto;

readonly class FunctionCall {

  public function __construct(
    public string $name,
    public string $arguments
  ) {}

}
