<?php

namespace Johannes85\AiBundle\LLM\OpenAi\Dto;

class ChatCompletionRequest {

  public function __construct(
    private string $model,
    private array $messages
  ) {}

  public function getModel(): string {
    return $this->model;
  }

  public function setModel(string $model): ChatCompletionRequest {
    $this->model = $model;
    return $this;
  }

  public function getMessages(): array {
    return $this->messages;
  }

  public function setMessages(array $messages): ChatCompletionRequest {
    $this->messages = $messages;
    return $this;
  }

}
