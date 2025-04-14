<?php

namespace AiBundle\LLM\DeepSeek\Dto;

use AiBundle\LLM\LLMUsage;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class Usage {

  public function __construct(
    #[SerializedName('prompt_tokens')] public int $promptTokens,
    #[SerializedName('completion_tokens')] public int $completionTokens,
    #[SerializedName('total_tokens')] public int $totalTokens
  ) {}

  /**
   * Converts object to LLMUsage object
   *
   * @return LLMUsage
   */
  public function toLLMUsage(): LLMUsage {
    return new LLMUsage(
      $this->promptTokens,
      $this->completionTokens,
      1
    );
  }

}
