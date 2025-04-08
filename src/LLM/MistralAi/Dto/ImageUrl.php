<?php

namespace AiBundle\LLM\MistralAi\Dto;

readonly class ImageUrl {

  public function __construct(
    public string $url,
    public ?string $detail = null
  ) {}

}
