<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class OpenAiTool {

  public function __construct(
    public OpenAiFunction $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
