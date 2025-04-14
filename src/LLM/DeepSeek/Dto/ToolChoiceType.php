<?php

namespace AiBundle\LLM\DeepSeek\Dto;

enum ToolChoiceType: string {

  case AUTO = 'auto';
  case REQUIRED = 'required';
  case FUNCTION = 'function';

}
