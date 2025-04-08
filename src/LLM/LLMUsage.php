<?php

namespace AiBundle\LLM;

readonly class LLMUsage {

  public function __construct(
    public int $inputTokens,
    public int $outputTokens
  ) {}

}
