<?php

namespace AiBundle\LLM\GoogleAi\Dto;

class Content {

  /**
   * @param array<Part> $parts
   * @param string|null $role
   */
  public function __construct(
    public readonly array $parts = [],
    public readonly ?string $role = null,
  ) {}

}
