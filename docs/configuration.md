# Symfony Configuraton

## Full example configuration
```yaml
ai:
  llms:
    open_ai:
      default:
        apikey: '...'
        model: 'gpt-4o-mini'
        timeout: 30 # (optional) Timeout value in seconds, default: 300
        endpoint: 'https://override.endpoint' # (optional) Allows to override the default endpoint, default: https://api.openai.com/v1
      second_instance:
        apikey: '...'
        model: 'o3-mini'
    google_ai:
      default:
        apikey: '...'
        model: 'gemini-2.0-flash'
        timeout: 30 # (optional) Timeout value in seconds, default: 300
    mistral_ai:
      default:
        apikey: '...'
        model: 'mistral-small-latest'
        timeout: 30 # (optional) Timeout value in seconds, default: 300
    anthropic:
      default:
        apikey: '...'
        model: 'claude-3-5-sonnet-20241022'
        timeout: 30 # (optional) Timeout value in seconds, default: 300
    ollama:
      default:
        model: 'gemma3:latest'
        timeout: 30 # (optional) Timeout value in seconds, default: 300
        endpoint: 'https://override.endpoint' # (optional) Allows to override the default endpoint, default: http://127.0.0.1:11434
    deep_seek:
      default:
        apikey: '...'
        model: 'deepseek-chat'
        timeout: 30 # (optional) Timeout value in seconds, default: 300
  mcp_servers:
    example_everything:
      stdio_transport:
        command: ['docker', 'run', '--rm','-i', 'mcp/everything']
        stop_signal: 'SIGINT' # See: https://www.php.net/manual/en/pcntl.constants.php > SIG_* constants
        response_timeout: # Time to wait in seconds for a response from the MCP server, default: 20
    example_github:
      streamable_http_transport:
        endpoint: 'https://api.githubcopilot.com/mcp/'
        headers:
          Authorization: 'Bearer ...'
```

## LLM instances

The LLM instances are registered as services in the Symfony container. The service IDs are generated based on the configuration keys. For example, the service ID for the `open_ai` default instance is `ai_bundle.llm.open_ai`.

If the configuration key is not called "default", the service ID will be generated as `ai_bundle.llm.<llm_name>.<config_key>`. For example, the service ID for the `open_ai` instance `second_instance` is `ai_bundle.llm.open_ai.second_instance`.

In addition to the service registered with a specific ID, the default instance of each backend is registered as the corresponding LLM class. For example, the `ai_bundle.llm.open_ai` service is also registered as `AiBundle\LLM\OpenAi\OpenAi`. This allows you to use the LLM class directly without having to specify the service ID.

## MCP server instances

The MCP server instances are also registered as services in the Symfony container. The service IDs are generated based on the configuration keys. For example, the service ID for the `example_everything` MCP server is `ai_bundle.mcp_server.example_everything`.
