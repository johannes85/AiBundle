<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class GoogleAiTool {

  /**
   * @param array<FunctionDeclaration> $functionDeclarations
   */
  public function __construct(
    public array $functionDeclarations
  ) {}

}
