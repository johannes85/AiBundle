<?php

namespace AiBundle\LLM\OpenAi\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class ChatCompletionRequest {

  /**
   * @var array<mixed>|null
   */
  #[SerializedName('response_format')]
  private ?array $responseFormat = null;

  /**
   * @param string $model
   * @param array<AbstractOpenAiMessage> $messages
   */
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

  /**
   * @return array<AbstractOpenAiMessage>
   */
  public function getMessages(): array {
    return $this->messages;
  }

  /**
   * @param array<AbstractOpenAiMessage> $messages
   * @return $this
   */
  public function setMessages(array $messages): ChatCompletionRequest {
    $this->messages = $messages;
    return $this;
  }

  /**
   * @return array<mixed>|null
   */
  public function getResponseFormat(): ?array {
    return $this->responseFormat;
  }

  /**
   * @param array<mixed>|null $response_format
   * @return static
   */
  public function setResponseFormat(?array $response_format): static {
    $this->responseFormat = $response_format;
    return $this;
  }

}
