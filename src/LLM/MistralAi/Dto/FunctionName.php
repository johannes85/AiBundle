<?php

namespace AiBundle\LLM\MistralAi\Dto;

readonly class FunctionName {

  public function __construct(
    public string $name
  ) { }

}
