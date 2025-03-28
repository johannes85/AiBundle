<?php

namespace AiBundle\LLM\MistralAi\Dto;

class ChatCompletionResponse {

  /**
   * @param array<ChatCompletionChoice> $choices
   */
  public function __construct(
    public readonly array $choices
  ) {}

}
