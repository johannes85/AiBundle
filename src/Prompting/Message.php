<?php

namespace AiBundle\Prompting;

class Message {

  public function __construct(
    private MessageRole $role,
    private string $content,
    private bool $usePlaceholders = true
  ) {
  }

  public function getRole(): MessageRole {
    return $this->role;
  }

  public function setRole(MessageRole $role): Message {
    $this->role = $role;
    return $this;
  }

  public function getContent(): string {
    return $this->content;
  }

  public function setContent(string $content): Message {
    $this->content = $content;
    return $this;
  }

  /**
   * @return bool
   */
  public function getUsePlaceholders(): bool {
    return $this->usePlaceholders;
  }

  /**
   * @param bool $usePlaceholders
   * @return static
   */
  public function setUsePlaceholders(bool $usePlaceholders): static {
    $this->usePlaceholders = $usePlaceholders;
    return $this;
  }

  /**
   * Applies placeholders to message content and returns new copy of message with placeholders applied.
   *
   * @param array<string, scalar> $placeholders
   * @return $this
   */
  public function applyPlaceholders(array $placeholders): Message {
    $message = clone $this;
    if ($this->usePlaceholders) {
      $message->setContent(preg_replace_callback(
        '/{{(.*?)}}/',
        fn(array $m) => $placeholders[$m[1]] ?? '',
        $message->getContent()
      ));
      $message->setUsePlaceholders(false);
    }
    return $message;
  }

}
