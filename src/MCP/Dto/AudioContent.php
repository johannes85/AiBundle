<?php

namespace AiBundle\MCP\Dto;

readonly class AudioContent extends Content {

  public function __construct(
    public string $data,
    public string $mimeType
  ) {
    parent::__construct('audio');
  }

}
