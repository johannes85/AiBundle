<?php

namespace AiBundle\LLM;

use AiBundle\Prompting\Message;

abstract class AbstractLLM {

  /**
   * Generates completion
   *
   * @param array<Message> $messages
   * @return LLMResponse
   */
  public abstract function generate(array $messages): LLMResponse;

  /**
   * Generates completion with structured data response
   *
   * @param array<Message> $messages
   * @param string $datatype
   * @return LLMDataResponse
   */
  public abstract function generateData(array $messages, string $datatype): LLMDataResponse;

}
