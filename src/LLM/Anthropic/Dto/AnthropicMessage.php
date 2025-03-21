<?php

namespace AiBundle\LLM\Anthropic\Dto;

use AiBundle\LLM\Ollama\Dto\OllamaMessage;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use InvalidArgumentException;

class AnthropicMessage {

  public function __construct(
    public readonly string $role,
    public readonly string $content
  ) {}

  /**
   * Creates new AnthropicMessage from message
   *
   * @param Message $message
   * @return self
   */
  public static function fromMessage(Message $message): self {
    return new self(
      match ($message->role) {
        MessageRole::AI => 'assistant',
        MessageRole::HUMAN => 'user',
        default => throw new InvalidArgumentException(
          'Anthropic message doesn\'t support type: ' . $message->role->name
        )
      },
      $message->content
    );
  }

}
