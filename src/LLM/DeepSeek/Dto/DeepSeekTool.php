<?php

namespace AiBundle\LLM\DeepSeek\Dto;

readonly class DeepSeekTool {

  public function __construct(
    public DeepSeekFunction $function
  ) {}

  public function getType(): string {
    return 'function';
  }

}
