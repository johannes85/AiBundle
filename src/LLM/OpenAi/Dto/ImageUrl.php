<?php

namespace AiBundle\LLM\OpenAi\Dto;

class ImageUrl {

  public function __construct(
    public readonly string $url,
    public readonly ?DetailLevel $detail = null
  ) {}

}
