<?php

namespace AiBundle\LLM\Anthropic\Dto;

use AiBundle\LLM\LLMUsage;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class Usage {

  public function __construct(
    #[SerializedName('cache_creation_input_tokens')] public int $cacheCreationInputTokens,
    #[SerializedName('cache_read_input_tokens')] public int $cacheReadInputTokens,
    #[SerializedName('input_tokens')] public int $inputTokens,
    #[SerializedName('output_tokens')] public int $outputTokens
  ) {}

  /**
   * Converts object to LLMUsage object
   *
   * @return LLMUsage
   */
  public function toLLMUsage(): LLMUsage {
    return new LLMUsage(
      $this->inputTokens,
      $this->outputTokens
    );
  }

}
