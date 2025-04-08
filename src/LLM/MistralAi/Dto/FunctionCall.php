<?php

namespace AiBundle\LLM\MistralAi\Dto;

readonly class FunctionCall {

  public function __construct(
    public string $name,
    public string $arguments
  ) {}

}
