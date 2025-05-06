<?php

namespace AiBundle\LLM\MistralAi\Dto;

use AiBundle\Prompting\Tools\AbstractTool;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Prompting\Tools\ToolsHelperException;
use AiBundle\Serializer\EmptyObjectHelper;

readonly class MistralFunction {

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
   * @param AbstractTool $tool
   * @param ToolsHelper $tools
   * @return self
   * @throws ToolsHelperException
   */
  public static function fromTool(AbstractTool $tool, ToolsHelper $tools): self {
    return new self(
      $tool->name,
      $tool->description,
      EmptyObjectHelper::injectEmptyObjects($tools->getToolCallbackSchema($tool))
    );
  }

}
