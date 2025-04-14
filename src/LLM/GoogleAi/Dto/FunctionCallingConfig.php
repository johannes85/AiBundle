<?php

namespace AiBundle\LLM\GoogleAi\Dto;

use AiBundle\LLM\LLMCapabilityException;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolChoice;

readonly class FunctionCallingConfig {

  /**
   * @param FunctionCallingMode $mode
   * @param array<string>|null $allowedFunctionNames
   */
  public function __construct(
    public FunctionCallingMode $mode,
    public ?array $allowedFunctionNames = null
  ) { }

  /**
   * Creates instance for toolbox
   *
   * @param Toolbox $toolbox
   * @return self
   * @throws LLMCapabilityException
   */
  public static function forToolbox(Toolbox $toolbox): self {
    return match(true) {
      $toolbox->toolChoice === ToolChoice::AUTO => new self(FunctionCallingMode::AUTO),
      $toolbox->toolChoice === ToolChoice::FORCE_TOOL_USAGE => new self(FunctionCallingMode::ANY),
      /** @phpstan-ignore match.alwaysTrue */
      is_string($toolbox->toolChoice) => new self(FunctionCallingMode::ANY, [$toolbox->toolChoice]),
      default => throw new LLMCapabilityException('Tool choice configuration of toolbox isn\'t supported by this LLM')
    };
  }

}
