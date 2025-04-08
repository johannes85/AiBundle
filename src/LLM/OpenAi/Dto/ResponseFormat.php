<?php

namespace AiBundle\LLM\OpenAi\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class ResponseFormat {

  public function __construct(
    public string $type,
    #[SerializedName('json_schema')] public ?JsonSchema $jsonSchema
  ) {}

}
