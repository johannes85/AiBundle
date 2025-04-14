<?php

namespace AiBundle\LLM\GoogleAi\Dto;

class GenerateContentParameters {

  /** @var array<GoogleAiTool>|null */
  private ?array $tools = null;

  private ?ToolConfig $toolConfig = null;

  /**
   * @param array<Content> $contents
   * @param Content|null $systemInstruction
   * @param GenerationConfig|null $generationConfig
   */
  public function __construct(
    private array $contents,
    private ?Content $systemInstruction = null,
    private ?GenerationConfig $generationConfig = null
  ) {}

  /**
   * @return array<Content>
   */
  public function getContents(): array {
    return $this->contents;
  }

  /**
   * @param array<Content> $contents
   * @return $this
   */
  public function setContents(array $contents): static {
    $this->contents = $contents;
    return $this;
  }

  public function getSystemInstruction(): ?Content {
    return $this->systemInstruction;
  }

  public function setSystemInstruction(?Content $systemInstruction): static {
    $this->systemInstruction = $systemInstruction;
    return $this;
  }

  public function getGenerationConfig(): ?GenerationConfig {
    return $this->generationConfig;
  }

  public function setGenerationConfig(?GenerationConfig $generationConfig): static {
    $this->generationConfig = $generationConfig;
    return $this;
  }

  /**
   * @return array<GoogleAiTool>|null
   */
  public function getTools(): ?array {
    return $this->tools;
  }

  /**
   * @param array<GoogleAiTool>|null $tools
   * @return $this
   */
  public function setTools(?array $tools): static {
    $this->tools = $tools;
    return $this;
  }

  public function getToolConfig(): ?ToolConfig {
    return $this->toolConfig;
  }

  public function setToolConfig(?ToolConfig $toolConfig): static {
    $this->toolConfig = $toolConfig;
    return $this;
  }

}
