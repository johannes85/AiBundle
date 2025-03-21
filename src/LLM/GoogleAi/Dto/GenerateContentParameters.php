<?php

namespace AiBundle\LLM\GoogleAi\Dto;

class GenerateContentParameters {

  /**
   * @param Content[] $contents
   * @param Content|null $systemInstruction
   * @param GenerationConfig|null $generationConfig
   */
  public function __construct(
    public readonly array $contents,
    public readonly ?Content $systemInstruction = null,
    public readonly ?GenerationConfig $generationConfig = null
  ) {}

}
