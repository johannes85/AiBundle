<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class ToolChoice {

  public function __construct(
    public ToolChoiceType $type,
    #[SerializedName('disable_parallel_tool_use')] public ?bool $disableParallelToolUse = null,
    public ?string $name = null,
  ) {}

}
