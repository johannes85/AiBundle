<?php

namespace AiBundle\LLM\OpenAi\Dto;

use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Prompting\Tools\ToolsHelperException;

readonly class OpenAiFunction {

  /**
   * @param string $name
   * @param string $description
   * @param array<mixed> $parameters
   * @param bool $strict
   */
  public function __construct(
    public string $name,
    public string $description,
    public array $parameters,
    public bool $strict = false
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
