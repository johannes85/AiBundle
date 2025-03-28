<?php

namespace AiBundle\LLM\MistralAi\Dto;

class ChatCompletionMessage {

  public function __construct(
    public readonly string $content
  ) {}

}
