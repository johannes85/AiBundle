<?php

namespace AiBundle\MCP\Server;

readonly class RegisteredTool {

  /**
   * @param string $serviceId
   * @param string $methodName
   * @param string $name
   * @param string $description
   * @param array<mixed> $schema
   */
  public function __construct(
    public string $serviceId,
    public string $methodName,
    public string $name,
    public string $description,
    public array $schema
  ) { }

}
