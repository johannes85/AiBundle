<?php

namespace AiBundle\LLM\DeepSeek\Dto;

readonly class ChatCompletionChoice {

  public function __construct(
    public DeepSeekMessage $message
  ) {}

}
