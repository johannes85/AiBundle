<?php

namespace Johannes85\AiBundle\LLM\Ollama\Dto;

use Johannes85\AiBundle\LLM\Ollama\Dto\AbstractGenerateParameters;

class GenerateParameters extends AbstractGenerateParameters {

  private string $prompt;

  public function getPrompt(): string {
    return $this->prompt;
  }

  public function setPrompt(string $prompt): GenerateParameters {
    $this->prompt = $prompt;
    return $this;
  }

}
