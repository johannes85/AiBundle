<?php

namespace AiBundle\LLM\MistralAi\Dto;

use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Tools;
use AiBundle\Prompting\Tools\ToolsException;

class MistralFunction {

  public function __construct(
    public readonly string $name,
    public readonly string $description,
    public readonly array $parameters,
    public readonly bool $strict = false
  ) {}

  /**
   * Creates new instance from Tool
   *
   * @param Tool $tool
   * @param Tools $tools
   * @return self
   * @throws ToolsException
   */
  public static function fromTool(Tool $tool, Tools $tools): self {
    return new self(
      $tool->name,
      $tool->description,
      $tools->getToolCallbackSchema($tool)
    );
  }

}
