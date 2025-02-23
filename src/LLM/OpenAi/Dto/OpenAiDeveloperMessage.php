<?php

namespace Johannes85\AiBundle\LLM\OpenAi\Dto;

class OpenAiDeveloperMessage extends AbstractOpenAiMessage {

  /**
   * @inheritDoc
   *
   * @return string
   */
  public function getRole(): string {
    return 'developer';
  }

}
