<?php

namespace AiBundle\LLM\MistralAi\Dto;

enum ContentPartType: string {

  case TEXT = 'text';
  case IMAGE_URL = 'image_url';
  case DOCUMENT_URL = 'document_url';
  case REFERENCE = 'reference';

}
