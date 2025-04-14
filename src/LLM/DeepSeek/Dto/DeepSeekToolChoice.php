<?php

namespace AiBundle\LLM\DeepSeek\Dto;

use AiBundle\LLM\LLMCapabilityException;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolChoice;

readonly class DeepSeekToolChoice {

  public function __construct(
    public ToolChoiceType $type,
    public ?FunctionName $function = null,
  ) {}

  /**
   * Creates instance for toolbox
   *
   * @param Toolbox $toolbox
   * @return self|ToolChoiceType
   * @throws LLMCapabilityException
   */
  public static function forToolbox(Toolbox $toolbox): self|ToolChoiceType {
    return match(true) {
      $toolbox->toolChoice === ToolChoice::AUTO => ToolChoiceType::AUTO,
      $toolbox->toolChoice === ToolChoice::FORCE_TOOL_USAGE => ToolChoiceType::REQUIRED,
      /** @phpstan-ignore match.alwaysTrue */
      is_string($toolbox->toolChoice) => new self(ToolChoiceType::FUNCTION, function: new FunctionName($toolbox->toolChoice)),
      default => throw new LLMCapabilityException('Tool choice configuration of toolbox isn\'t supported by this LLM')
    };
  }

}
