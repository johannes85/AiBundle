<?php

namespace AiBundle\Prompting;

class Messages implements MessagesInterface {

  /** @var array<Message> */
  private readonly array $messages;

  public function __construct(Message ...$messages) {
    $this->messages = $messages;
  }

  /**
   * @inheritDoc
   */
  public function processMessages(array $placeholders = []): array {
    $ret = [];
    foreach ($this->messages as $message) {
      $message->applyPlaceholders($placeholders);
      $ret[] = $message->applyPlaceholders($placeholders);
    }
    return $ret;
  }

}


