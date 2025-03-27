<?php

namespace AiBundle\LLM\GoogleAi\Dto;

class Part {

  private ?string $text = null;
  private ?InlineData $inlineData = null;
  private ?object $functionCall = null;
  private ?object $functionResponse = null;
  private ?object $fileData = null;
  private ?object $executableCode = null;
  private ?object $codeExecutionResult = null;

  /**
   * @return string|null
   */
  public function getText(): ?string {
    return $this->text;
  }

  /**
   * @param string|null $text
   * @return static
   */
  public function setText(?string $text): static {
    $this->text = $text;
    return $this;
  }

  /**
   * @return InlineData|null
   */
  public function getInlineData(): ?InlineData {
    return $this->inlineData;
  }

  /**
   * @param InlineData|null $inlineData
   * @return static
   */
  public function setInlineData(?InlineData $inlineData): static {
    $this->inlineData = $inlineData;
    return $this;
  }

  /**
   * @return object|null
   */
  public function getFunctionCall(): ?object {
    return $this->functionCall;
  }

  /**
   * @param object|null $functionCall
   * @return static
   */
  public function setFunctionCall(?object $functionCall): static {
    $this->functionCall = $functionCall;
    return $this;
  }

  /**
   * @return object|null
   */
  public function getFunctionResponse(): ?object {
    return $this->functionResponse;
  }

  /**
   * @param object|null $functionResponse
   * @return static
   */
  public function setFunctionResponse(?object $functionResponse): static {
    $this->functionResponse = $functionResponse;
    return $this;
  }

  /**
   * @return object|null
   */
  public function getFileData(): ?object {
    return $this->fileData;
  }

  /**
   * @param object|null $fileData
   * @return static
   */
  public function setFileData(?object $fileData): static {
    $this->fileData = $fileData;
    return $this;
  }

  /**
   * @return object|null
   */
  public function getExecutableCode(): ?object {
    return $this->executableCode;
  }

  /**
   * @param object|null $executableCode
   * @return static
   */
  public function setExecutableCode(?object $executableCode): static {
    $this->executableCode = $executableCode;
    return $this;
  }

  /**
   * @return object|null
   */
  public function getCodeExecutionResult(): ?object {
    return $this->codeExecutionResult;
  }

  /**
   * @param object|null $codeExecutionResult
   * @return static
   */
  public function setCodeExecutionResult(?object $codeExecutionResult): static {
    $this->codeExecutionResult = $codeExecutionResult;
    return $this;
  }

}
