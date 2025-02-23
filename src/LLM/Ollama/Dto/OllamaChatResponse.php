<?php

namespace Johannes85\AiBundle\LLM\Ollama\Dto;

class OllamaChatResponse {

  private OllamaMessage $message;

  public function getMessage(): OllamaMessage {
    return $this->message;
  }

  public function setMessage(OllamaMessage $message): OllamaChatResponse {
    $this->message = $message;
    return $this;
  }

}
