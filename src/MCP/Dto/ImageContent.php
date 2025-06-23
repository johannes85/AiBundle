<?php

namespace AiBundle\MCP\Dto;

readonly class ImageContent extends Content {

  public function __construct(
    public string $data,
    public string $mimeType
  ) {
    parent::__construct('image');
  }

}
