<?php

namespace AiBundle\LLM\OpenAi\Dto;

class OpenAiTool {

  public function __construct(
    public readonly OpenAiFunction $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
