<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class FunctionCall {

  /**
   * @param string $name
   * @param array<mixed> $args
   */
  public function __construct(
    public string $name,
    public array $args
  ) {}

}
