<?php

namespace Johannes85\AiBundle\LLM\Ollama\Dto;

use Johannes85\AiBundle\LLM\Ollama\Dto\AbstractGenerateParameters;

class GenerateChatParameters extends AbstractGenerateParameters {

  private bool $stream = false;

  /**
   * @param string $model
   * @param OllamaMessage[] $messages
   */
  public function __construct(
    private string $model,
    private array $messages
  ) {}

  /**
   * @return OllamaMessage[]
   */
  public function getMessages(): array {
    return $this->messages;
  }

  /**
   * @param OllamaMessage[] $messages
   * @return $this
   */
  public function setMessages(array $messages): GenerateChatParameters {
    $this->messages = $messages;
    return $this;
  }

  public function getModel(): string {
    return $this->model;
  }

  public function setModel(string $model): GenerateChatParameters {
    $this->model = $model;
    return $this;
  }

  public function getStream(): bool {
    return $this->stream;
  }

  public function setStream(bool $stream): GenerateChatParameters {
    $this->stream = $stream;
    return $this;
  }

}
