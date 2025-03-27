<?php

namespace AiBundle\Prompting;

interface MessagesInterface {

  /**
   * Builds message instances for prompting usage by replacing placeholders with provided values.
   *
   * @param array<string, scalar> $placeholders
   * @return array<Message>
   */
  public function processMessages(array $placeholders = []): array;

}
