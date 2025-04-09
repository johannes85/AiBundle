<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

readonly class LLMResponse {

  public function __construct(
    public Message $message,
    public LLMUsage $usage,
    public ?object $data = null,
  ) {}

}
