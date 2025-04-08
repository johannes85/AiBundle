<?php

namespace AiBundle\LLM\MistralAi\Dto;

readonly class ChatCompletionResponse {

  /**
   * @param array<ChatCompletionChoice> $choices
   */
  public function __construct(
    public array $choices
  ) {}

}
