<?php

namespace AiBundle\MCP\Server;

class ToolRegistry {

  /** @var array<RegisteredTool> */
  private array $tools = [];

  /**
   * @param string $serviceId
   * @param string $methodName
   * @param string $name
   * @param string $description
   * @param array<mixed> $schema
   * @return void
   */
  public function registerTool(
    string $serviceId,
    string $methodName,
    string $name,
    string $description,
    array $schema
  ): void {
    $this->tools[$name] = new RegisteredTool(
      $serviceId,
      $methodName,
      $name,
      $description,
      $schema
    );
  }

  /**
   * Returns list of registered tools
   *
   * @return array<RegisteredTool>
   */
  public function getTools(): array {
    return $this->tools;
  }

  /**
   * Returns registered tool by name
   *
   * @param string $name
   * @return RegisteredTool|null
   */
  public function getTool(string $name): ?RegisteredTool {
    return $this->tools[$name] ?? null;
  }

}
