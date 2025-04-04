<?php

namespace AiBundle\LLM\MistralAi\Dto;

class MistralTool {

  public function __construct(
    public readonly MistralFunction $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
