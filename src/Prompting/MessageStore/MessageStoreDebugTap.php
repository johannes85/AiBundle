<?php

namespace AiBundle\Prompting\MessageStore;

use AiBundle\Prompting\Message;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

class MessageStoreDebugTap implements MessageStoreInterface {

  public function __construct(
    private readonly MessageStoreInterface $messageStore,
    private readonly OutputInterface $output
  ) {
  }

  /**
   * @inheritDoc
   */
  public function store(Uuid $uid, array $messages): void {
    $this->messageStore->store($uid, $messages);
    $this->output->writeln(sprintf(
      'Stored %d messages to %s: %s',
      $count = count($messages),
      $uid,
      $count === 0 ? '[]' : ''
    ));
    foreach ($messages as $message) {
      $this->output->writeln(
        "-----------------------------------\n".
        preg_replace('/^/m', '  ', (string)$message).
        "\n-----------------------------------"
      );
    }
  }

  /**
   * @inheritDoc
   */
  public function retrieve(Uuid $uid): array {
    $messages = $this->messageStore->retrieve($uid);
    $this->output->writeln(sprintf(
      'Retrieved %d messages from %s: %s',
      $count = count($messages),
      $uid,
      $count === 0 ? '[]' : ''
    ));
    foreach ($messages as $message) {
      $this->output->writeln(
        "-----------------------------------\n".
        preg_replace('/^/m', '  ', (string)$message).
        "\n-----------------------------------"
      );
    }
    return $messages;
  }

  /**
   * @inheritDoc
   */
  public function delete(Uuid $uid): void {
    $this->output->writeln(sprintf(
      'Deleted messages for uid %s',
      $uid
    ));
    $this->messageStore->delete($uid);
  }

}
