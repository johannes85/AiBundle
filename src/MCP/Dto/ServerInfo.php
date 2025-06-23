<?php

namespace AiBundle\MCP\Dto;

readonly class ServerInfo {

  public function __construct(
    public string $name,
    public string $title,
    public string $version
  ) {}

}
