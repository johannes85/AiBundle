<?php

namespace AiBundle\LLM\MistralAi\Dto;

class ChatCompletionChoice {

  public function __construct(
    public readonly ChatCompletionMessage $message
  ) {}

}
