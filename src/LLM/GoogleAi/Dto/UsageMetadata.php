<?php

namespace AiBundle\LLM\GoogleAi\Dto;

class UsageMetadata {

  public function __construct(
    public readonly int $promptTokenCount,
    public readonly int $candidatesTokenCount,
    public readonly int $totalTokenCount
  ) {}

}
