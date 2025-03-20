<?php

namespace AiBundle\LLM\OpenAi\Dto;

class ChatCompletionMessage {

  private string $content;

  public function getContent(): string {
    return $this->content;
  }

  public function setContent(string $content): ChatCompletionMessage {
    $this->content = $content;
    return $this;
  }

}
