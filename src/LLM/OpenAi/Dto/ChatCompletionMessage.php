<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class ChatCompletionMessage {

  public function __construct(
    public string $content
  ) {}

}
