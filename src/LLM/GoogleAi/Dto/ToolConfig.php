<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class ToolConfig {

  public function __construct(
    public FunctionCallingConfig $functionCallingConfig
  ) { }

}
