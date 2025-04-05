<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class FunctionResponse {

  /**
   * @param string $name
   * @param array<mixed> $response
   */
  public function __construct(
    public string $name,
    public array $response
  ) {}

}
