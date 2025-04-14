<?php

namespace AiBundle\LLM\DeepSeek\Dto;

class ChatCompletionResponse {

  /**
   * @param array<ChatCompletionChoice> $choices
   * @param Usage $usage
   */
  public function __construct(
    public array $choices,
    public Usage $usage
  ) {}

}
