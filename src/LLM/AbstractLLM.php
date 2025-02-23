<?php

namespace Johannes85\AiBundle\LLM;

use Johannes85\AiBundle\Prompting\Message;

abstract class AbstractLLM {

  /**
   * Generates completion
   *
   * @param Message[] $messages
   * @return LLMResponse
   */
  public abstract function generate(array $messages): LLMResponse;

}
