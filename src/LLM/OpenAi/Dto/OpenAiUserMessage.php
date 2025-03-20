<?php

namespace AiBundle\LLM\OpenAi\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class OpenAiUserMessage extends AbstractOpenAiMessage {

  /**
   * @inheritDoc
   *
   * @return string
   */
  public function getRole(): string {
    return 'user';
  }

}
