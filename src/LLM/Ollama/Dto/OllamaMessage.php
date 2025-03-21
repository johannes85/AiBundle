<?php

namespace AiBundle\LLM\Ollama\Dto;

use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use InvalidArgumentException;

class OllamaMessage {

  public function __construct(
    public readonly string $role,
    public readonly string $content
  ) {}

  /**
   * Creates new Ollama messsage from message
   *
   * @param Message $message
   * @return OllamaMessage
   */
  public static function fromMessage(Message $message): OllamaMessage {
    return new self(
      match ($message->role) {
        MessageRole::AI => 'assistant',
        MessageRole::HUMAN => 'user',
        MessageRole::SYSTEM => 'system'
      },
      $message->content
    );
  }

  /**
   * Creates message from Ollama message
   *
   * @return Message
   */
  public function toMessage(): Message {
    return (new Message(
      match ($this->role) {
        'assistant' => MessageRole::AI,
        'user' => MessageRole::HUMAN,
        'system' => MessageRole::SYSTEM,
        default => throw new InvalidArgumentException('Invalid role of Ollama message' . $this->role)
      },
      $this->content
    ));
  }

}
