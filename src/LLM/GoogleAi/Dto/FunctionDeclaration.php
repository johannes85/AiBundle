<?php

namespace AiBundle\LLM\GoogleAi\Dto;

use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Prompting\Tools\ToolsHelperException;

readonly class FunctionDeclaration {

  /**
   * @param string $name
   * @param string $description
   * @param array<mixed> $parameters
   */
  public function __construct(
    public string $name,
    public string $description,
    public array $parameters
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
