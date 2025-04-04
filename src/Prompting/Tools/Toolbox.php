<?php

namespace AiBundle\Prompting\Tools;

class Toolbox {

  /** @var array<Tool> */
  private array $tools = [];

  public function __construct(Tool ...$tools) {
    foreach ($tools as $tool) {
      $this->tools[$tool->name] = $tool;
    }
  }

  /**
   * Returns tool by its name
   *
   * @param string $name
   * @return Tool|null
   */
  public function getTool(string $name): ?Tool {
    return $this->tools[$name] ?? null;
  }

  /**
   * Returns all tools
   *
   * @return array<Tool>
   */
  public function getTools(): array {
    return array_values( $this->tools);
  }

}
