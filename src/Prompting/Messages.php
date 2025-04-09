<?php

namespace AiBundle\Prompting;

readonly class Messages implements MessagesInterface {

  /** @var array<Message> */
  private array $messages;

  public function __construct(Message ...$messages) {
    $this->messages = $messages;
  }

  /**
   * @inheritDoc
   */
  public function processMessages(array $placeholders = []): array {
    return array_map(fn(Message $message) => $message->applyPlaceholders($placeholders), $this->messages);
  }

}


