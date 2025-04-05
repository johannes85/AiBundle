<?php

namespace AiBundle\LLM\Anthropic\Dto;

enum ContentBlockType: string {

  case TOOL_USE = 'tool_use';
  case TOOL_RESULT = 'tool_result';
  case TEXT = 'text';
  case THINKING = 'thinking';
  case REDACTED_THINKING = 'redacted_thinking';
  case IMAGE = 'image';

}
