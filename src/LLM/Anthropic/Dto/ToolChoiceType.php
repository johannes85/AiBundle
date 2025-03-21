<?php

namespace AiBundle\LLM\Anthropic\Dto;

enum ToolChoiceType: string {

  case AUTO = 'auto';
  case ANY = 'any';
  case TOOL = 'tool';
  case NONE = 'none';

}
