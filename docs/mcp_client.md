# MCP Client

Calling tools provided by an MCP server via the stdio and Streamable HTTP transport is supported.

## Setup

### Register MCP endpoints

#### Via stdio transport
```yaml
ai:
  mcp:
    endpoints:
      example_everything:
        stdio_transport:
          command: ['docker', 'run', '--rm','-i', 'mcp/everything']
          stop_signal: 'SIGINT' # See: https://www.php.net/manual/en/pcntl.constants.php > SIG_* constants
```

#### Via Streamable HTTP transport

Note: text/event-stream responses are not supported at the moment.

```yaml
ai:
  mcp:
    endpoints:
      example_github:
        streamable_http_transport:
          endpoint: 'https://api.githubcopilot.com/mcp/'
          headers:
            Authorization: 'Bearer ...'
```

### Load tools from endpoint and add them to a toolbox

```php
use AiBundle\MCP\Client\MCPEndpoint;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Toolbox;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Example {

  public function __construct(
    #[Autowire('@ai_bundle.mcp_endpoint.example_everything')] private MCPEndpoint $mcp,
  ) {
    parent::__construct($container);
  }
  
  public function callLLM(): void {
    $mcpToolbox = new Toolbox(
      [...$this->mcp->getTools()]
    );
    
    $res = $this->llm->generate(
      [
        new Message(MessageRole::HUMAN, 'Add 1 and 2')
      ],
      toolbox: $mcpToolbox
    );
    
    $output->writeln($res->message->content);
  }

}
```
