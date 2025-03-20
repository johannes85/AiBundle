<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

abstract class AbstractLLM {

  /**
   * Generates completion
   *
   * @param array<Message> $messages
   * @param GenerateOptions|null $options
   * @return LLMResponse
   */
  public abstract function generate(array $messages, ?GenerateOptions $options = null): LLMResponse;

  /**
   * Generates completion with structured data response
   *
   * @param array<Message> $messages
   * @param string $datatype
   * @param GenerateOptions|null $options
   * @return LLMDataResponse
   */
  public abstract function generateData(
    array $messages, string $datatype, ?GenerateOptions $options = null
  ): LLMDataResponse;

}
