# MCP Server 

The MCPHandler allows to expose service methods as MCP tools via Streamable HTTP transport.

## Setup

### Configure MCP server

Although it's not required, it is recommended to configure your MCP server:

```yaml
ai:
  mcp:
    server:
      name: ExampleServer
      title: Example Server Display Name
      version: 1.0.0
      instructions: 'Optional instructions for the client'
```

### Register controller

Create an MCP server endpoint by registering a Symfony controller:

```php
use AiBundle\MCP\Server\MCPHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

class MCPController extends AbstractController {

  #[Route('/mcp', name: 'mcp_handler', methods: ['GET', 'POST'])]
  public function handleMcpRequest(
    MCPHandler $mcpHandler,
    Request $request,
  ): Response {
    return $mcpHandler->handleRequest($request);
  }

}
```

Since a normal Symfony controller is used, you can use the full power of Symfony features like access control.

### Defining MCP tools

To define MCP tools, you can use the `@MCPTool` annotation on your service methods. 
The service class itself must have the `@ContainsMCPTools` annotation in order to be recognized as a source of MCP tools.

```php
use AiBundle\MCP\Server\Attribute\ContainsMCPTools;
use AiBundle\MCP\Server\Attribute\MCPTool;
use AiBundle\Json\Attributes\Description;

#[ContainsMCPTools]
class Tools {

  #[MCPTool(
    name: 'addNumbers', // Optional, defaults to method name
    description: 'Adds two numbers together' // Optional
  )]
  public function addNumbers(
    #[Description('The first number')] int $a, 
    #[Description('The second number')] int $b
  ): int {
    return $a + $b;
  }

}
```

The schema for the MCP tool will be generated automatically based on the method signature and the attributes used.

See the [Schema Generator](schema_generator.md) documentation for more information about how to add additional information to the schema.


