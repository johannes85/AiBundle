<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class AnthropicTool {

  /**
   * @param string $name
   * @param string $description
   * @param array<mixed> $inputSchema
   */
  public function __construct(
    public string $name,
    public string $description,
    #[SerializedName('input_schema')] public array $inputSchema
  ) { }

}
