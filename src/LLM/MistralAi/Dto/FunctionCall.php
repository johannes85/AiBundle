<?php

namespace AiBundle\LLM\MistralAi\Dto;

class FunctionCall {

  public function __construct(
    public readonly string $name,
    public readonly string $arguments
  ) {}

}
