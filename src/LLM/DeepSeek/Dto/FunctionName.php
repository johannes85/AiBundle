<?php

namespace AiBundle\LLM\DeepSeek\Dto;

readonly class FunctionName {

  public function __construct(
    public string $name
  ) { }

}
