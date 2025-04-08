<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class GoogleAiResponse {

  /**
   * @param array<Candidate> $candidates
   */
  public function __construct(
    public array $candidates,
    public UsageMetadata $usageMetadata
  ) {}

}
