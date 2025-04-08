<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class ChatCompletionResponse {

  /**
   * @param array<ChatCompletionChoice> $choices
   */
  public function __construct(
    public array $choices,
    public Usage $usage
  ) {}

}
