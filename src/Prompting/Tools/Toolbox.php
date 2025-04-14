<?php

namespace AiBundle\Prompting\Tools;

use AiBundle\LLM\LLMException;

class Toolbox {

  private const DEFAULT_MAX_LLM_CALLS = 10;

  /** @var array<Tool> */
  private array $tools = [];

  /**
   * @param array<Tool> $tools
   */
  public function __construct(
    array $tools,
    public readonly string|ToolChoice $toolChoice = ToolChoice::AUTO,
    private readonly int $maxLLMCalls = self::DEFAULT_MAX_LLM_CALLS
  ) {
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
    return array_values($this->tools);
  }

  /**
   * Raises exception if the number of LLM calls exceeds the maximum allowed.
   *
   * @param int $callNumber
   * @return void
   * @throws LLMException
   */
  public function ensureMaxLLMCalls(int $callNumber): void {
    if ($callNumber > $this->maxLLMCalls) {
      throw new LLMException(sprintf(
        'Maximum number of %d LLM calls exceeded.%s',
        $this->maxLLMCalls,
        $this->maxLLMCalls === self::DEFAULT_MAX_LLM_CALLS
          ? ' This is set to a conservative value by default to avoid accidental overuse. You can set a higher value with setMaxLLMCalls(...) of your Toolbox instance.'
          : '',
      ));
    }
  }

}
