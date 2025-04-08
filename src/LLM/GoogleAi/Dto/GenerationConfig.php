<?php

namespace AiBundle\LLM\GoogleAi\Dto;

use AiBundle\LLM\GenerateOptions;
use InvalidArgumentException;

class GenerationConfig {

  /** @var array<string> */
  private ?array $stopSequences = null;

  private ?int $maxOutputTokens = null;
  private ?float $temperature = null;
  private ?float $topP = null;
  private ?int $topK = null;
  private ?string $responseMimeType = null;

  /** @var array<mixed>|null */
  private ?array $responseSchema = null;

  /**
   * Create instance from LLM GenerateOptions
   *
   * @param ?GenerateOptions $options
   * @return self
   */
  public static function fromGenerateOptions(?GenerateOptions $options = null): self {
    $ret = new self();
    if ($options !== null) {
      $ret
        ->setTemperature($options->getTemperature())
        ->setMaxOutputTokens($options->getMaxOutputTokens())
        ->setTopK($options->getTopK())
        ->setTopP($options->getTopP());
      foreach ($options->getCustomOptions() as $key => $value) {
        $method = 'set' . ucfirst($key);
        if (!method_exists($ret, $method)) {
          throw new InvalidArgumentException(sprintf('Unknown custom option "%s"', $key));
        }
        $ret->$method($value);
      }
    }
    return $ret;
  }

  /**
   * @return array<string>|null
   */
  public function getStopSequences(): ?array {
    return $this->stopSequences;
  }

  /**
   * @param array<string>|null $stopSequences
   * @return static
   */
  public function setStopSequences(?array $stopSequences): static {
    $this->stopSequences = $stopSequences;
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
   * @return static
   */
  public function setMaxOutputTokens(?int $maxOutputTokens): static {
    $this->maxOutputTokens = $maxOutputTokens;
    return $this;
  }

  /**
   * @return float|null
   */
  public function getTemperature(): ?float {
    return $this->temperature;
  }

  /**
   * @param float|null $temperature
   * @return static
   */
  public function setTemperature(?float $temperature): static {
    $this->temperature = $temperature;
    return $this;
  }

  /**
   * @return float|null
   */
  public function getTopP(): ?float {
    return $this->topP;
  }

  /**
   * @param float|null $topP
   * @return static
   */
  public function setTopP(?float $topP): static {
    $this->topP = $topP;
    return $this;
  }

  /**
   * @return int|null
   */
  public function getTopK(): ?int {
    return $this->topK;
  }

  /**
   * @param int|null $topK
   * @return static
   */
  public function setTopK(?int $topK): static {
    $this->topK = $topK;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getResponseMimeType(): ?string {
    return $this->responseMimeType;
  }

  /**
   * @param string|null $responseMimeType
   * @return static
   */
  public function setResponseMimeType(?string $responseMimeType): static {
    $this->responseMimeType = $responseMimeType;
    return $this;
  }

  /**
   * @return array<mixed>|null
   */
  public function getResponseSchema(): ?array {
    return $this->responseSchema;
  }

  /**
   * @param array<mixed>|null $responseSchema
   * @return static
   */
  public function setResponseSchema(?array $responseSchema): static {
    $this->responseSchema = $responseSchema;
    return $this;
  }

}
