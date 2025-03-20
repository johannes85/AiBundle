<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

class LLMResponse {

  public function __construct(
    private Message $message
  ) {}

  public function getMessage(): Message {
    return $this->message;
  }

  public function setMessage(Message $message): LLMResponse {
    $this->message = $message;
    return $this;
  }

}
