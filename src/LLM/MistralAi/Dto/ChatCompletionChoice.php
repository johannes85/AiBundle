<?php

namespace AiBundle\LLM\MistralAi\Dto;

readonly class ChatCompletionChoice {

  public function __construct(
    public MistralAiMessage $message
  ) {}

}
