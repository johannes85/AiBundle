<?php

namespace AiBundle\LLM\GoogleAi\Dto;

readonly class Candidate {

  public function __construct(
    public Content $content
  ) {}

}
