<?php

namespace AiBundle\MCP\Dto;

readonly class InitializeResponse {

  public function __construct(
    public string $protocolVersion,
    public Capabilities $capabilities,
    public ServerInfo $serverInfo,
    public string $instructions
  ) {}

}
