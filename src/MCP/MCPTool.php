<?php

namespace AiBundle\MCP;

use AiBundle\MCP\Dto\ToolDefinition;
use AiBundle\Prompting\Tools\AbstractTool;

readonly class MCPTool extends AbstractTool {

  /**
   * @param string $name
   * @param string $description
   * @param array<mixed> $schema
   * @param MCPServer $server
   */
  public function __construct(
    string $name,
    string $description,
    public array $schema,
    private MCPServer $server
  ) {
    parent::__construct($name, $description);
  }

  /**
   * Creates MCPTool instance from ToolDefinition and server
   *
   * @param ToolDefinition $toolDefinition
   * @param MCPServer $server
   * @return self
   */
  public static function fromToolDefinition(
    ToolDefinition $toolDefinition,
    MCPServer $server
  ): self {
    return new self(
      $toolDefinition->name,
      $toolDefinition->description,
      $toolDefinition->inputSchema,
      $server
    );
  }

  /**
   * Executes tool
   *
   * @param array<mixed> $arguments
   * @return string
   * @throws MCPException
   */
  public function call(array $arguments): string {
    $res = $this->server->callTool($this->name, $arguments);
    $contentString = json_encode($res->content);
    if ($res->isError) {
      throw new MCPException('Error while executing MCP tool: ' . $contentString);
    }
    return $contentString;
  }

}
