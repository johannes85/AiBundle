<?php

namespace AiBundle\LLM\OpenAi\Dto;

readonly class ImageUrl {

  public function __construct(
    public string $url,
    public ?DetailLevel $detail = null
  ) {}

}
