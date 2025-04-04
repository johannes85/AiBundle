<?php

namespace AiBundle\LLM\MistralAi\Dto;

use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Prompting\Tools\ToolsHelperException;

class MistralFunction {

  /**
   * @param string $name
   * @param string $description
   * @param array<mixed> $parameters
   * @param bool $strict
   */
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
   * @param ToolsHelper $tools
   * @return self
   * @throws ToolsHelperException
   */
  public static function fromTool(Tool $tool, ToolsHelper $tools): self {
    return new self(
      $tool->name,
      $tool->description,
      $tools->getToolCallbackSchema($tool)
    );
  }

}
