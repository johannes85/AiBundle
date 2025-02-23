<?php

namespace Johannes85\AiBundle\Prompting;

class Message {

  public function __construct(
    private MessageRole $role,
    private string $content
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

}
