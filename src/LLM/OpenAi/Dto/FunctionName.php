<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class FunctionName {

  public function __construct(
    public string $name
  ) { }

}
