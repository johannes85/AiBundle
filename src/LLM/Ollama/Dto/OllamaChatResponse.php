<?php

namespace AiBundle\LLM\Ollama\Dto;

use AiBundle\LLM\LLMUsage;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class OllamaChatResponse {

  public function __construct(
    public OllamaMessage $message,
    #[SerializedName('prompt_eval_count')] public int $promptEvalCount,
    #[SerializedName('eval_count')] public int $evalCount
  ) {}

  /**
   * Converts object to LLMUsage object
   *
   * @return LLMUsage
   */
  public function getLLMUsage(): LLMUsage {
    return new LLMUsage(
      $this->promptEvalCount,
      $this->evalCount
    );
  }

}
