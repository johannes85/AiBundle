<?php

namespace Johannes85\AiBundle\LLM;

abstract class AbstractLLM {

  public abstract function generate(string $prompt): string;

}
