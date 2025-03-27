<?php

namespace AiBundle\Prompting;

use AiBundle\LLM\LLMResponse;
use AiBundle\Prompting\MessageStore\MessageStoreInterface;
use Symfony\Component\Uid\Uuid;

class PersistentMessages implements MessagesInterface {

  /** @var array<Message> */
  private array $messages;

  public readonly Uuid $sessionUid;

  /**
   * @param MessageStoreInterface $messageStore
   * @param array<Message> $systemMessages
   * @param Uuid|null $sessionUid
   */
  public function __construct(
    private readonly MessageStoreInterface $messageStore,
    private array $systemMessages = [],
    ?Uuid $sessionUid = null
  ) {
    $this->sessionUid = $sessionUid ?? Uuid::v4();
    $this->messages = $this->messageStore->retrieve($this->sessionUid);
  }

  /**
   * @inheritDoc
   */
  public function processMessages(array $placeholders = []): array {
    $ret = [];
    foreach ($this->systemMessages as $message) {
      $ret[] = $message->applyPlaceholders($placeholders);
    }
    $processedMessages = [];
    foreach ($this->messages as $message) {
      $ret[] = $message->applyPlaceholders($placeholders);
      $processedMessages[] = $message;
    }
    $this->messages = $processedMessages;
    return $ret;
  }

  /**
   * Adds new message
   *
   * @param Message $message
   * @return $this
   */
  public function addMessage(Message $message): static {
    $this->messages[] = $message;
    return $this;
  }

  /**
   * Applies LLM response and persists messages
   *
   * @param LLMResponse $response
   * @return void
   */
  public function applyResponseAndPersist(LLMResponse $response): void {
    $this->addMessage($response->message);
    $this->messageStore->store($this->sessionUid, $this->messages);
  }


}
