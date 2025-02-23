<?php 

namespace Johannes85\AiBundle\LLM\Ollama\Dto;

abstract class AbstractGenerateParameters {

  private string $model;

  private bool $stream = false;

  public function getModel(): string {
    return $this->model;
  }

  public function setModel(string $model): AbstractGenerateParameters {
    $this->model = $model;
    return $this;
  }

  public function isStream(): bool {
    return $this->stream;
  }

  public function setStream(bool $stream): AbstractGenerateParameters {
    $this->stream = $stream;
    return $this;
  }

}
