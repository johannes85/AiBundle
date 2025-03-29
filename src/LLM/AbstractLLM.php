<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

abstract class AbstractLLM {

  /**
   * Generates completion
   *
   * @param array<Message> $messages
   * @param GenerateOptions|null $options
   * @param string|null $responseDataType
   * @return LLMResponse
   * @throws LLMException
   */
  public abstract function generate(
    array $messages,
    ?GenerateOptions $options = null,
    ?string $responseDataType = null
  ): LLMResponse;

}
