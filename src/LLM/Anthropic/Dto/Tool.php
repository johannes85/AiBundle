<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class Tool {

  /**
   * @param string $name
   * @param string $description
   * @param array<mixed> $inputSchema
   */
  public function __construct(
    public readonly string $name,
    public readonly string $description,
    #[SerializedName('input_schema')] public readonly array $inputSchema
  ) { }

}
