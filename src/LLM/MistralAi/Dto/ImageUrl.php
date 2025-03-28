<?php

namespace AiBundle\LLM\MistralAi\Dto;

class ImageUrl {

  public function __construct(
    public readonly string $url,
    public readonly ?string $detail = null
  ) {}

}
