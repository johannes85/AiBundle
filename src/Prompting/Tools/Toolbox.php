<?php

namespace AiBundle\Prompting\Tools;

use AiBundle\LLM\LLMException;

class Toolbox {

  private const DEFAULT_MAX_LLM_CALLS = 10;

  /** @var array<AbstractTool> */
  private array $tools = [];

  /**
   * @param array<AbstractTool> $tools
   */
  public function __construct(
    array $tools,
    public readonly string|ToolChoice $toolChoice = ToolChoice::AUTO,
    private readonly int $maxLLMCalls = self::DEFAULT_MAX_LLM_CALLS,
    private readonly bool $ignoreInvalidToolChoice = true
  ) {
    foreach ($tools as $tool) {
      $this->tools[$tool->name] = $tool;
    }
  }

  /**
   * Returns tool by its name
   *
   * @param string $name
   * @return AbstractTool|null
   */
  public function getTool(string $name): ?AbstractTool {
    if (!isset($this->tools[$name])) {
      return $this->ignoreInvalidToolChoice ? new CallbackTool(
        'not_found',
        'Tool not found',
        fn () => sprintf('Tool "%s" not found', $name)
      ) : null;
    }
    return $this->tools[$name];
  }

  /**
   * Returns all tools
   *
   * @return array<AbstractTool>
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
