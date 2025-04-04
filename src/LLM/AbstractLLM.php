<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;
use AiBundle\Prompting\Tools\Tool;

abstract class AbstractLLM {

  /**
   * Generates completion
   *
   * @param array<Message> $messages
   * @param GenerateOptions|null $options
   * @param string|null $responseDataType
   * @param array<Tool>|null $tools
   * @return LLMResponse
   * @throws LLMException
   */
  public abstract function generate(
    array $messages,
    ?GenerateOptions $options = null,
    ?string $responseDataType = null,
    ?array $tools = null
  ): LLMResponse;

}
