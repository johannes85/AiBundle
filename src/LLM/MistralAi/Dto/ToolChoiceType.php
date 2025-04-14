<?php

namespace AiBundle\LLM\MistralAi\Dto;

enum ToolChoiceType: string {

  case AUTO = 'auto';
  case ANY = 'any';
  case FUNCTION = 'function';

}
