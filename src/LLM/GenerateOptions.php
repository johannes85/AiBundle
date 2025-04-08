<?php

namespace AiBundle\LLM;

class GenerateOptions {

  private ?float $temperature = null;
  private ?int $maxOutputTokens = null;
  private ?int $topK = null;
  private ?float $topP = null;

  /** @var array<string, mixed> */
  private array $customOptions = [];

  /**
   * @return float|null
   */
  public function getTemperature(): ?float {
    return $this->temperature;
  }

  /**
   * @param float|null $temperature
   * @return GenerateOptions
   */
  public function setTemperature(?float $temperature): GenerateOptions {
    $this->temperature = $temperature;
    return $this;
  }

  /**
   * @return int|null
   */
  public function getMaxOutputTokens(): ?int {
    return $this->maxOutputTokens;
  }

  /**
   * @param int|null $maxOutputTokens
   * @return GenerateOptions
   */
  public function setMaxOutputTokens(?int $maxOutputTokens): GenerateOptions {
    $this->maxOutputTokens = $maxOutputTokens;
    return $this;
  }

  public function getTopK(): ?int {
    return $this->topK;
  }

  public function setTopK(?int $topK): GenerateOptions {
    $this->topK = $topK;
    return $this;
  }

  public function getTopP(): ?float {
    return $this->topP;
  }

  public function setTopP(?float $topP): GenerateOptions {
    $this->topP = $topP;
    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getCustomOptions(): array {
    return $this->customOptions;
  }

  /**
   * @param array<string, mixed> $customOptions
   * @return GenerateOptions
   */
  public function setCustomOptions(array $customOptions): GenerateOptions {
    $this->customOptions = $customOptions;
    return $this;
  }

}
