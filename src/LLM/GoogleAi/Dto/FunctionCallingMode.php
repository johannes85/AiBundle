<?php

namespace AiBundle\LLM\GoogleAi\Dto;

enum FunctionCallingMode: string {

  case AUTO = 'auto';
  case ANY = 'any';
  case NONE = 'none';

}
