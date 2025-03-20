<?php

namespace AiBundle\LLM\OpenAi\Dto;

class ChatCompletionResponse {

  /** @var array<ChatCompletionChoice> */
  private array $choices;

  /**
   * @return array<ChatCompletionChoice>
   */
  public function getChoices(): array {
    return $this->choices;
  }

  /**
   * @param array<ChatCompletionChoice> $choices
   * @return $this
   */
  public function setChoices(array $choices): ChatCompletionResponse {
    $this->choices = $choices;
    return $this;
  }

}
