<?php

namespace AiBundle\LLM\MistralAi\Dto;

readonly class MistralTool {

  public function __construct(
    public MistralFunction $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
