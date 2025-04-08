<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class Content {

  /**
   * @param array<Part> $parts
   * @param string|null $role
   */
  public function __construct(
    public array $parts = [],
    public ?string $role = null,
  ) {}

}
