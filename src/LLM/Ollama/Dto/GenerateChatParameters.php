<?php

namespace AiBundle\LLM\Ollama\Dto;

use AiBundle\LLM\Ollama\Dto\AbstractGenerateParameters;
use AiBundle\LLM\Ollama\Dto\OllamaOptions;
use AiBundle\LLM\Ollama\Ollama;
use LLM\Ollama\Dto\OllamaFunctionTool;

class GenerateChatParameters {

  private bool $stream = false;

  /** @var array<mixed>|null */
  private ?array $format = null;

  /**
   * @var array<OllamaTool>|null
   */
  private ?array $tools = null;

  private ?OllamaOptions $options = null;

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

  /**
   * @return array<mixed>|null
   */
  public function getFormat(): ?array {
    return $this->format;
  }

  /**
   * @param array<mixed>|null $format
   * @return static
   */
  public function setFormat(?array $format): static {
    $this->format = $format;
    return $this;
  }

  /**
   * @return array<OllamaTool>|null
   */
  public function getTools(): ?array {
    return $this->tools;
  }

  /**
   * @param array<OllamaTool>|null $tools
   * @return $this
   */
  public function setTools(?array $tools): GenerateChatParameters {
    $this->tools = $tools;
    return $this;
  }

  public function getOptions(): ?OllamaOptions {
    return $this->options;
  }

  public function setOptions(?OllamaOptions $options): static {
    $this->options = $options;
    return $this;
  }

}
