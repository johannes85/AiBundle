<?php

namespace Johannes85\AiBundle\LLM\OpenAi\Dto;

use Johannes85\AiBundle\Prompting\Message;
use Johannes85\AiBundle\Prompting\MessageRole;

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
   * @return static
   */
  public static function fromMessage(Message $message): static {
    $class = match ($message->getRole()) {
      MessageRole::SYSTEM => OpenAiDeveloperMessage::class,
      MessageRole::HUMAN => OpenAiUserMessage::class,
      MessageRole::AI => OpenAiAssistantMessage::class
    };
    return new $class($message->getContent());
  }

}
