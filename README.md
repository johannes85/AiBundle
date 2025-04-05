# PHP Symfony Ai Bundle

![example workflow](https://github.com/johannes85/AiBundle/actions/workflows/symfony-bundle.yml/badge.svg)

This PHP Symfony bundle allows to call LLM backends in a generic and simple way.

The following backends are supported:

| Backend    | Text generation | Image processing | Tool calling | Info                       
|------------|---|---|---|----------------------------|
| OpenAI     | ✅ | ✅ | ✅ | https://openai.com/        |
| Ollama     | ✅ | ✅ | ✅ | https://ollama.ai/         |
| GoogleAI   | ✅ | ✅ | ❌ | https://ai.google.dev      |
| Anthropic  | ✅ | ✅ | ❌ | https://www.anthropic.com/ |
| Mistral AI | ✅ | ✅ | ✅ | https://mistral.ai/        |

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
  public string $capital =>
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

## Usage

### Configure LLM backend instances
To use a specific backend as a service, it has to be configured:

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
```

In this example, the following services will be registered:
- ai_bundle.llm.open_ai
- ai_bundle.llm.open_ai.o3mini
- ai_bundle.llm.google_ai
- ai_bundle.llm.mistral_ai
- ai_bundle.llm.anthropic
- ai_bundle.llm.ollama

When configuring the "default" instance of a llm, in addition to the id, the class itself (e.g. AiBundle\LLM\OpenAi\OpenAi) will be registered as a service.

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

Before using the examples, you have to set the api key for the corresponding backend as an environment variable:
```bash
export MISTRAL_AI_APIKEY=...
export ANTHROPIC_APIKEY=...
export GOOGLE_AI_APIKEY=...
export OPEN_AI_APIKEY=...
```
