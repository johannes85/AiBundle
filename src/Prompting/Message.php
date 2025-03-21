<?php

namespace AiBundle\Prompting;

class Message {

  public function __construct(
    public readonly MessageRole $role,
    public readonly string $content,
    private bool $placeholderProcessing = true
  ) {}

  /**
   * Applies placeholders to message content and returns new copy of message with placeholders applied.
   *
   * @param array<string, scalar> $placeholders
   * @return self
   */
  public function applyPlaceholders(array $placeholders): self {
    return new self(
      $this->role,
      $this->placeholderProcessing ? preg_replace_callback(
        '/{{(.*?)}}/',
        fn(array $m) => $placeholders[$m[1]] ?? '',
        $this->content
      ) : $this->content,
      false
    );
  }

}
