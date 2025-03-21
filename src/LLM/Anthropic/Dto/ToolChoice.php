<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class ToolChoice {

  public function __construct(
    public readonly ToolChoiceType $type,
    #[SerializedName('disable_parallel_tool_use')] public readonly ?bool $disableParallelToolUse = null,
    public readonly ?string $name = null,
  ) {}

}
