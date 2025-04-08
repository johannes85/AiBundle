<?php

namespace AiBundle\LLM\GoogleAi\Dto;

use AiBundle\LLM\LLMUsage;

readonly class UsageMetadata {

  public function __construct(
    public int $promptTokenCount,
    public int $candidatesTokenCount,
    public int $totalTokenCount
  ) {}

  /**
   * Converts object to LLMUsage object
   *
   * @return LLMUsage
   */
  public function toLLMUsage(): LLMUsage {
    return new LLMUsage(
      $this->promptTokenCount,
      $this->candidatesTokenCount
    );
  }

}
