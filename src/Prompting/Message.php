<?php

namespace AiBundle\Prompting;

use Stringable;

readonly class Message implements Stringable {

  /**
   * @param MessageRole $role
   * @param string $content
   * @param bool $placeholderProcessing
   * @param array<File> $files
   */
  public function __construct(
    public MessageRole $role,
    public string $content,
    private bool $placeholderProcessing = false,
    public array $files = []
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

  /**
   * @inheritDoc
   */
  public function __toString() {
    return sprintf(
      "Type: %s\nContent: %s\nProcess placeholders: %s",
      $this->role->name,
      $this->content,
      $this->placeholderProcessing ? 'Yes' : 'No'
    );
  }

}
