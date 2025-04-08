<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class ChatCompletionChoice {

  public function __construct(
    public OpenAiMessage $message
  ) {}

}
