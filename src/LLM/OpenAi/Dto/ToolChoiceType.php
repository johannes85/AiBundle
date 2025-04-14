<?php

namespace AiBundle\LLM\OpenAi\Dto;

enum ToolChoiceType: string {

  case AUTO = 'auto';
  case REQUIRED = 'required';
  case FUNCTION = 'function';

}
