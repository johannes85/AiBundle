<?php

namespace AiBundle\LLM\GoogleAi\Dto;

class Candidate {

  public function __construct(
    public readonly Content $content
  ) {}

}
