# Symfony AI Bundle

![example workflow](https://github.com/johannes85/AiBundle/actions/workflows/symfony-bundle.yml/badge.svg)

This PHP Symfony bundle allows to call LLM backends like OpenAI, Ollama etc. in a generic and simple way.

It also provides an MCP client and server with tool calling capabilities.
This allows you to easily expose your Symfony services as MCP tools or call MCP tools provided by an MCP server.

The following backends are supported:

| Backend    | Text generation | Image processing | Tool calling | MCP Tool calling |Info                       
|------------|---|---|-----|---|----------------------------|
| OpenAI     | ✅ | ✅ | ✅   | ✅ | https://openai.com/        |
| Ollama     | ✅ | ✅ | ✅*1 | ✅ | https://ollama.ai/         |
| GoogleAI   | ✅ | ✅ | ✅   | ✅ | https://ai.google.dev      |
| Anthropic  | ✅ | ✅ | ✅   | ✅ | https://www.anthropic.com/ |
| Mistral AI | ✅ | ✅ | ✅   | ✅ | https://mistral.ai/        |
| DeepSeek   | ✅ | ❌ | ✅   | ✅ | https://www.deepseek.com/  |

The **OpenAI** endpoint URL can be changed so it is possible to access different backends with an OpenAI compatible API.
But be aware that not all features are supported by all backends.  
 => [Feedack](https://github.com/johannes85/AiBundle/issues/new) regarding the compatibility of the different backends is welcome.

- *1: Tool choice settings other than ToolChoice::AUTO aren't supported by Ollama.

## Requirements
- PHP >=8.2

## Features

### Backend independent generating:
```php

$messages = [
  new Message(MessageRole::HUMAN, 'Tell me about Canada')
];

$res = $opanAi->generate($messages);
...
$res = $ollama->generate($messages);
...
```

### Image processing:
```php
$messages = [
  new Message(
    MessageRole::HUMAN,
    'What is the content of this image?',
    files: [File::fromPath(FileType::IMAGE, 'image/jpg', __DIR__.'/image.jpg')]
  )
];
$res = $llm->generate($messages);
```

### Persistent message history:
See example: ```AiBundle\Examples\PersistentChatCommand```

#### The following store backends are supported:
- PSR-6 compatible file cache: ```AiBundle\Prompting\MessageStore\Psr6CacheMessageStore```

### Support for structured responses with deserialization in an object instance:

```php
class CountryInfo {
  public string $name;

  // Usage of setter for further processing/validation of the set data
  private string $capital;
  public function setCapital(string $capital) {
    $this->capital = $capital;
  }

  // Array member type hinting via Attribute
  #[ArrayType(itemType: 'string')] public array $languages; 
}

$info = $llm->generate([
  new Message(MessageRole::HUMAN, 'Tell me about Canada')
], responseDataType: CountryInfo::class);

/* Result:
CountryInfo#248 (3) {
  public string $name =>
  string(6) "Canada"
  private string $capital =>
  string(6) "Ottawa"
  public array $languages =>
  array(2) {
    [0] =>
    string(7) "English"
    [1] =>
    string(6) "French"
  }
}
*/
```

More information about how to define the schema of the response data type can be found in the [Schema Generator](docs/schema_generator.md) documentation.

### Tool calling support

```php
$res = $this->llm->generate(
  [
    new Message(
      MessageRole::HUMAN,
      'What is the current weather in Karlsruhe (49° 1′ N , 8° 24′ O) and Stuttgart (48° 47′ N, 9° 11′ O), Germany'
    )
  ],
  toolbox: new Toolbox(
    [
      new CallbackTool(
        'getWeather',
        'Retrieves current weather',
        function (
          #[Description('Latitude, for example 52.52')] string $latitude,
          #[Description('Longitude, for example 13.41')] string $longitude
        ) use ($output) {
          return file_get_contents(
            'https://api.open-meteo.com/v1/forecast?latitude=' . $latitude . '&longitude=' . $longitude . '&current=temperature,windspeed'
          );
        }
      )
    ],
    toolChoice: ToolChoice::AUTO, // Can be also ToolChoice::FORCE_TOOL_USAGE or the name of a tool to enforce usage
    maxLLMCalls: 10 // Maximal number of LLM calls, set to a sensible value to avoid infinite loops or expensive calls. Default: 10
  )
)
```

More information about how to define the schema of the tool callback function be found in the [Schema Generator](docs/schema_generator.md) documentation.

### MCP tool calling

Calling tools provided by an MCP server via the stdio and Streamable HTTP transport is supported.

See the [MCP Client documentation](docs/mcp_client.md) for more information.

### MCP server

The MCP server allows to expose service methods as MCP tools via Streamable HTTP transport. It can be used to create a custom MCP server that can be called by LLMs or other clients.

See the [MCP Server documentation](docs/mcp_server.md) for more information.

## Usage

### Configure LLM backend instances
To use a specific backend as a service, it has to be registered in the bundle config:

```yaml
# ai.yaml
ai:
  llms:
    open_ai:
      default:
        apikey: '...'
        model: 'gpt-4o-mini'
      o3mini:
        apikey: '...'
        model: 'o3-mini'
    google_ai:
      default:
        apikey: '...'
        model: 'gemini-2.0-flash'
    mistral_ai:
      default:
        apikey: '...'
        model: 'mistral-small-latest'
    anthropic:
      default:
        apikey: '...'
        model: 'claude-3-5-sonnet-20241022'
    ollama:
      default:
        model: 'gemma3:latest'
    deep_seek:
      default:
        apikey: '...'
        model: 'deepseek-chat'
```

In this example, the following services will be registered:
- ai_bundle.llm.open_ai
- ai_bundle.llm.open_ai.o3mini
- ai_bundle.llm.google_ai
- ai_bundle.llm.mistral_ai
- ai_bundle.llm.anthropic
- ai_bundle.llm.ollama
- ai_bundle.llm.deep_seek

When configuring the "default" instance of a llm, in addition to the ID, the class itself (e.g. AiBundle\LLM\OpenAi\OpenAi) will be registered as a service.

### Execute standalone examples
This bundle provides standalone examples of the features provided.
They can be executed by a central console command similar to the Symfony console:

```bash
composer install
php bin/console example:... --llm= ollama
```

You can get a list of available examples by executing:
```bash
php bin/console
```

The llm backend to use can be set via the ```--llm``` option.
The following values are supported:
- ollama (default)
- open_ai
- google_ai
- anthropic
- mistral_ai
- deep_seek

Before using the examples, you have to set the api key for the corresponding backend as an environment variable:
```bash
export MISTRAL_AI_APIKEY=...
export ANTHROPIC_APIKEY=...
export GOOGLE_AI_APIKEY=...
export OPEN_AI_APIKEY=...
export DEEP_SEEK_APIKEY=...
```
