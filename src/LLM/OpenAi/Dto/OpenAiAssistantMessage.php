<?php

namespace Johannes85\AiBundle\LLM\OpenAi\Dto;

class OpenAiAssistantMessage extends AbstractOpenAiMessage {

  /**
   * @inheritDoc
   *
   * @return string
   */
  public function getRole(): string {
    return 'user';
  }


}
