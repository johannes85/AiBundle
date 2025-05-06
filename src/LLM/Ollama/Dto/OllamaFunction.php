<?php

namespace AiBundle\LLM\Ollama\Dto;

use AiBundle\Prompting\Tools\AbstractTool;
use AiBundle\Prompting\Tools\ToolsHelper;
use AiBundle\Prompting\Tools\ToolsHelperException;
use AiBundle\Serializer\EmptyObjectHelper;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

readonly class OllamaFunction {

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
      EmptyObjectHelper::injectEmptyObjects($tools->getToolCallbackSchema($tool))
    );
  }

}
