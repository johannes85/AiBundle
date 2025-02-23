<?php

namespace Johannes85\AiBundle\LLM\Ollama\Dto;

use Johannes85\AiBundle\Prompting\Message;
use Johannes85\AiBundle\Prompting\MessageRole;

class OllamaMessage {

  public function __construct(
    private string $role,
    private string $content
  ) {}

  /**
   * Creates new Ollama messsage from message
   *
   * @param Message $message
   * @return OllamaMessage
   */
  public static function fromMessage(Message $message): OllamaMessage {
    return new self(
      match ($message->getRole()) {
        MessageRole::AI => 'assistant',
        MessageRole::HUMAN => 'user',
        MessageRole::SYSTEM => 'system'
      },
      $message->getContent()
    );
  }

  /**
   * Creates message from Ollama message
   *
   * @return Message
   */
  public function toMessage(): Message {
    return (new Message(
      match ($this->getRole()) {
        'assistant' => MessageRole::AI,
        'user' => MessageRole::HUMAN,
        'system' => MessageRole::SYSTEM
      },
      $this->content
    ));
  }

  public function getRole(): string {
    return $this->role;
  }

  public function setRole(string $role): OllamaMessage {
    $this->role = $role;
    return $this;
  }

  public function getContent(): string {
    return $this->content;
  }

  public function setContent(string $content): OllamaMessage {
    $this->content = $content;
    return $this;
  }

}
