<?php

namespace AiBundle\LLM\GoogleAi\Dto;

class GoogleAiResponse {

  /**
   * @param array<Candidate> $candidates
   */
  public function __construct(
    public readonly array $candidates,
    public readonly UsageMetadata $usageMetadata
  ) {}

}
