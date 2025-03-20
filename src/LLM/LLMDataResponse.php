<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

class LLMDataResponse extends LLMResponse {

  public function __construct(
    Message $message,
    private ?object $data
  ) {
    parent::__construct($message);
  }

  public function getData(): ?object {
    return $this->data;
  }

  public function setData(?object $data): static {
    $this->data = $data;
    return $this;
  }

}
