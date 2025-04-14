<?php

namespace AiBundle\LLM;

readonly class LLMUsage {

  public function __construct(
    public int $inputTokens,
    public int $outputTokens,
    public int $llmCalls
  ) {}

  /**
   * Adds another LLMUsage object to this one and returns a new one.
   *
   * @param LLMUsage $usage
   * @return LLMUsage
   */
  public function add(LLMUsage $usage): LLMUsage {
    return new LLMUsage(
      $this->inputTokens + $usage->inputTokens,
      $this->outputTokens + $usage->outputTokens,
      $this->llmCalls + $usage->llmCalls
    );
  }

  /**
   * Returns empty usage stats
   *
   * @return LLMUsage
   */
  public static function empty(): LLMUsage {
    return new LLMUsage(0, 0, 0);
  }

}
