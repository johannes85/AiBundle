<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

class LLMResponse {

  public function __construct(
    public readonly Message $message,
    public readonly LLMUsage $usage,
    public readonly ?object $data = null,
  ) {}

}
