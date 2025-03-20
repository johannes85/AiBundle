<?php

namespace AiBundle\Prompting;

class Messages {

  /** @var array<Message> */
  private array $messages = [];

  public function __construct(Message ...$messages) {
    $this->messages = $messages;
  }

  /**
   * Builds message instances for prompting usage by replacing placeholders with provided values.
   *
   * @param array<string, scalar> $placeholders
   * @return array<Message>
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


