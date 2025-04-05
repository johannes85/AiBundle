<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class UsageMetadata {

  public function __construct(
    public int $promptTokenCount,
    public int $candidatesTokenCount,
    public int $totalTokenCount
  ) {}

}
