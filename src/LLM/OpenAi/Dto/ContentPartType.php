<?php

namespace AiBundle\LLM\OpenAi\Dto;

enum ContentPartType: string {

  case TEXT = 'text';
  case IMAGE_URL = 'image_url';

}
