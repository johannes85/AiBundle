<?php

namespace AiBundle\LLM\MistralAi\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class ResponseFormat {

  public function __construct(
    public readonly string $type,
    #[SerializedName('json_schema')] public readonly ?JsonSchema $jsonSchema
  ) {}

}
