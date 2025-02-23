<?php

namespace Johannes85\AiBundle\LLM\OpenAi\Dto;

class ChatCompletionChoice {

  private ChatCompletionMessage $message;

  public function getMessage(): ChatCompletionMessage {
    return $this->message;
  }

  public function setMessage(ChatCompletionMessage $message): ChatCompletionChoice {
    $this->message = $message;
    return $this;
  }

}
