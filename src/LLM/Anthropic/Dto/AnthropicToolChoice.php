<?php

namespace AiBundle\LLM\Anthropic\Dto;

use AiBundle\LLM\LLMCapabilityException;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolChoice;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class AnthropicToolChoice {

  public function __construct(
    public ToolChoiceType $type,
    #[SerializedName('disable_parallel_tool_use')] public ?bool $disableParallelToolUse = null,
    public ?string $name = null,
  ) {}

  /**
   * Creates instance for toolbox
   *
   * @param Toolbox $toolbox
   * @return self
   * @throws LLMCapabilityException
   */
  public static function forToolbox(Toolbox $toolbox): self {
    return match(true) {
      $toolbox->toolChoice === ToolChoice::AUTO => new self(ToolChoiceType::AUTO),
      $toolbox->toolChoice === ToolChoice::FORCE_TOOL_USAGE => new self(ToolChoiceType::ANY),
      /** @phpstan-ignore match.alwaysTrue */
      is_string($toolbox->toolChoice) => new self(ToolChoiceType::TOOL, name: $toolbox->toolChoice),
      default => throw new LLMCapabilityException('Tool choice configuration of toolbox isn\'t supported by this LLM')
    };
  }

}
