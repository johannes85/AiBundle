<?php

namespace Johannes85\AiBundle\LLM;

use Johannes85\AiBundle\Prompting\Message;

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
