<?php

namespace AiBundle\LLM\GoogleAi\Dto;

use AiBundle\Json\SchemaManipulator;
use AiBundle\Prompting\Tools\AbstractTool;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Prompting\Tools\ToolsHelperException;
use AiBundle\Serializer\EmptyObjectHelper;

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
   * @param AbstractTool $tool
   * @param ToolsHelper $tools
   * @return self
   * @throws ToolsHelperException
   */
  public static function fromTool(AbstractTool $tool, ToolsHelper $tools): self {
    return new self(
      $tool->name,
      $tool->description,
      EmptyObjectHelper::injectEmptyObjects(
        SchemaManipulator::removeProperties(
          $tools->getToolCallbackSchema($tool),
          [
            '$schema',
            'additionalProperties',
          ]
        )
      )
    );
  }

}
