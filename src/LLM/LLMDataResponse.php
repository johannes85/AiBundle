<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

class LLMDataResponse extends LLMResponse {

  public function __construct(
    Message $message,
    ?object $data = null
  ) {
    parent::__construct($message, $data);
  }

}
