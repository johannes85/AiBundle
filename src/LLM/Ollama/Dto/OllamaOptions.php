<?php

namespace AiBundle\LLM\Ollama\Dto;

use AiBundle\LLM\GenerateOptions;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

class OllamaOptions {

  private ?int $microstat = null;
  #[SerializedName('microstat_eta')] private ?float $microstatEta = null;
  #[SerializedName('microstat_tau')] private ?float $microstatTau = null;
  #[SerializedName('num_ctx')] private ?int $numCtx = null;
  #[SerializedName('repeat_last_n')] private ?int $repeatLastN = null;
  #[SerializedName('repeat_penalty')] private ?float $repeatPenalty = null;
  private ?float $temperature = null;
  private ?int $seed = null;
  private ?string $stop = null;
  #[SerializedName('num_predict')] private ?int $numPredict = null;
  #[SerializedName('top_k')] private ?int $topK = null;
  #[SerializedName('top_p')] private ?float $topP = null;
  #[SerializedName('min_p')] private ?float $minP = null;

  /**
   * Create instance from LLM GenerateOptions
   *
   * @param GenerateOptions $options
   * @return self
   */
  public static function fromGenerateOptions(GenerateOptions $options): self {
    $ret = (new self())
      ->setTemperature($options->getTemperature())
      ->setNumPredict($options->getMaxOutputTokens());
    foreach ($options->getCustomOptions() as $key => $value) {
      $method = 'set'.ucfirst(preg_replace_callback('/_(\w)/', fn($m) => strtoupper($m[1]), $key));
      if (!method_exists($ret, $method)) {
        throw new InvalidArgumentException(sprintf('Unknown custom option "%s"', $key));
      }
      $ret->$method($value);
    }
    return $ret;
  }

  public function getMicrostat(): ?int {
    return $this->microstat;
  }

  public function setMicrostat(?int $microstat): static {
    $this->microstat = $microstat;
    return $this;
  }

  public function getMicrostatEta(): ?float {
    return $this->microstatEta;
  }

  public function setMicrostatEta(?float $microstatEta): static {
    $this->microstatEta = $microstatEta;
    return $this;
  }

  public function getMicrostatTau(): ?float {
    return $this->microstatTau;
  }

  public function setMicrostatTau(?float $microstatTau): static {
    $this->microstatTau = $microstatTau;
    return $this;
  }

  public function getNumCtx(): ?int {
    return $this->numCtx;
  }

  public function setNumCtx(?int $numCtx): static {
    $this->numCtx = $numCtx;
    return $this;
  }

  public function getRepeatLastN(): ?int {
    return $this->repeatLastN;
  }

  public function setRepeatLastN(?int $repeatLastN): static {
    $this->repeatLastN = $repeatLastN;
    return $this;
  }

  public function getRepeatPenalty(): ?float {
    return $this->repeatPenalty;
  }

  public function setRepeatPenalty(?float $repeatPenalty): static {
    $this->repeatPenalty = $repeatPenalty;
    return $this;
  }

  public function getTemperature(): ?float {
    return $this->temperature;
  }

  public function setTemperature(?float $temperature): static {
    $this->temperature = $temperature;
    return $this;
  }

  public function getSeed(): ?int {
    return $this->seed;
  }

  public function setSeed(?int $seed): static {
    $this->seed = $seed;
    return $this;
  }

  public function getStop(): ?string {
    return $this->stop;
  }

  public function setStop(?string $stop): static {
    $this->stop = $stop;
    return $this;
  }

  public function getNumPredict(): ?int {
    return $this->numPredict;
  }

  public function setNumPredict(?int $numPredict): static {
    $this->numPredict = $numPredict;
    return $this;
  }

  public function getTopK(): ?int {
    return $this->topK;
  }

  public function setTopK(?int $topK): static {
    $this->topK = $topK;
    return $this;
  }

  public function getTopP(): ?float {
    return $this->topP;
  }

  public function setTopP(?float $topP): static {
    $this->topP = $topP;
    return $this;
  }

  public function getMinP(): ?float {
    return $this->minP;
  }

  public function setMinP(?float $minP): static {
    $this->minP = $minP;
    return $this;
  }

}
