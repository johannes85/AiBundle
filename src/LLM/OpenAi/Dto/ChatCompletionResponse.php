<?php

namespace Johannes85\AiBundle\LLM\OpenAi\Dto;

class ChatCompletionResponse {

  /** @var ChatCompletionChoice[] */
  private array $choices;

  public function getChoices(): array {
    return $this->choices;
  }

  public function setChoices(array $choices): ChatCompletionResponse {
    $this->choices = $choices;
    return $this;
  }

}
