<?php

namespace AiBundle\Prompting\MessageStore;

use AiBundle\Prompting\Message;
use Symfony\Component\Uid\Uuid;

interface MessageStoreInterface {

  /**
   * Persists messages to store for a given unique identifier.
   *
   * @param Uuid $uid
   * @param array<Message> $messages
   * @return void
   */
  public function store(Uuid $uid, array $messages): void;

  /**
   * Retrieves messages from store for a given unique identifier.
   *
   * @param Uuid $uid
   * @return array<Message>
   */
  public function retrieve(Uuid $uid): array;

  /**
   * Removes messages from store for a given unique identifier.
   *
   * @param Uuid $uid
   * @return void
   */
  public function delete(Uuid $uid): void;

}
