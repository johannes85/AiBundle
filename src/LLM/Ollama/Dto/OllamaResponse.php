<?php

namespace Johannes85\AiBundle\LLM\Ollama\Dto;

class OllamaResponse {

  private string $response;

  public function getResponse(): string {
    return $this->response;
  }

  public function setResponse(string $response): OllamaResponse {
    $this->response = $response;
    return $this;
  }

}
