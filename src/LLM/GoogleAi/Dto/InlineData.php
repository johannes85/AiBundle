<?php

namespace AiBundle\LLM\GoogleAi\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class InlineData {

  public function __construct(
    #[SerializedName('mime_type')] public string $mimeType,
    public string $data
  ) {}

}
