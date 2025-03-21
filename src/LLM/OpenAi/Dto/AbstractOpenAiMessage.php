<?php

namespace AiBundle\LLM\OpenAi\Dto;

use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;

abstract class AbstractOpenAiMessage {

  public function __construct(
    public string $content
  ) {}

  /**
   * Returns the message role
   *
   * @return string
   */
  public abstract function getRole(): string;

  /**
   * Creates new OpenAi message from message
   *
   * @param Message $message
   * @return self
   */
  public static function fromMessage(Message $message): self {
    $class = match ($message->role) {
      MessageRole::SYSTEM => OpenAiDeveloperMessage::class,
      MessageRole::HUMAN => OpenAiUserMessage::class,
      MessageRole::AI => OpenAiAssistantMessage::class
    };
    return new $class($message->content);
  }

}
