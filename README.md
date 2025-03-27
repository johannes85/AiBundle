# PHP Symfony Ai Bundle

![example workflow](https://github.com/johannes85/AiBundle/actions/workflows/symfony-bundle.yml/badge.svg)

This PHP Symfony bundle allows to call LLM backends in a generic and simple way.

The following backends are supported:

| Backend      | Text generation | Image processing  | Info                             
|--------------|-----------------|-------------------|----------------------------------|
| OpenAI       | ✅               | ❌ (WIP)           | https://openai.com/              |
| Ollama       | ✅               | ✅                 | https://ollama.ai/               |
| GoogleAI     | ✅               | ✅                 | https://ai.google.dev            |
| Anthropic    | ✅               | ✅                 | https://www.anthropic.com/       |

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

$info = $llm->generateData([
  new Message(MessageRole::HUMAN, 'Tell me about Canada')
], CountryInfo::class);

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
To use a specific backend it has to be configured as a service in your projects service definition and can be injected in your code afterwards.

```yaml
# services.yaml
services:

  my.ollama:
    class: AiBundle\LLM\Ollama\Ollama:
    arguments:
      $endpoint: 'http://127.0.0.1:11434'
      $model: 'gemma2'

  my.openai:
    class: AiBundle\LLM\OpenAi\OpenAi:
    arguments:
      $apiKey: '...'
      $model: 'gpt-4o-mini'

  my.googleai:
    class: AiBundle\LLM\GoogleAi\GoogleAi:
    arguments:
      $apiKey: '...'
      $model: 'gemini-2.0-flash'

  my.anthrophic:
    class: AiBundle\LLM\Anthropic\Anthropic:
    arguments:
      $apiKey: '...'
      $model: 'claude-3-5-sonnet-20241022'
```

### Execute standalone examples
This bundle provides standalone examples of the features provided.
They can be executed by a central console command similar to the Symfony console:

```bash
composer install
php bin/console 
```

Before using, you have to configure the backend instances in the `config/services_example.yaml` file.
