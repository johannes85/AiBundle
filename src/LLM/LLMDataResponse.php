<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

class LLMDataResponse extends LLMResponse {

  public function __construct(
    Message $message,
    public readonly ?object $data
  ) {
    parent::__construct($message);
  }

}
