<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;
use AiBundle\Prompting\Tools\Toolbox;

abstract class AbstractLLM {

  /**
   * Generates completion
   *
   * @param array<Message> $messages
   * @param GenerateOptions|null $options
   * @param string|null $responseDataType
   * @param Toolbox|null $toolbox
   * @return LLMResponse
   * @throws LLMException
   */
  public abstract function generate(
    array $messages,
    ?GenerateOptions $options = null,
    ?string $responseDataType = null,
    ?Toolbox $toolbox = null
  ): LLMResponse;

}
