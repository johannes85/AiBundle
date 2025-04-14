# JSON Schema Generator

To generate a JSON schema for the LLMs to process, this bundle provides a schema generator.
It's used by for example tool calling or structured responses.

It's designed to create a schema from code like a function or object declaration with minimal usage of additional configuration values.

## Attributes

There are some attributes to add some information the code itself can't provide:

* `AiBundle\Json\Attribute\Description` A description of for a function parameter or class property.
* `AiBundle\Json\Attribute\ArrayType` The type of the array items of a function parameter or class property. 

## Required parameters or properties

By default, all parameters or properties are required. If you want to make a parameter or attribute optional, you can:

* For class properties make them nullable.
* Form function parameters set them to a default value.

## Limitations 

* Union types are not supported at the moment.

## Examples

### Structured responses

```php
use AiBundle\Json\Attributes\ArrayType
use AiBundle\Json\Attributes\Description

class MyObject {
  public string $attr1;
  public string $attr2;
}

class ResponseObject {
  #[Description('Short description of the member')]
  public string $simpleMember;

  // Usage of setter for further processing/validation of the set data
  private string $viaSetter;
  public function setViaSetter(string $value) {
    $this->viaSetter = $value;
  }

  // Array member type hinting via Attribute
  #[ArrayType(itemType: 'string')] 
  public array $stringArray; 
  
  // Array member type hinting via Attribute
  #[ArrayType(itemClass: MyObject::class)]
  public array $objectArray;
  
  public ?string $optionalMember;
}

$res = $llm->generate($messages, responseDataType: ResponseObject::class);
```

Internally generated schema:

```php
array:3 [
  "type" => "object"
  "properties" => array:5 [
    "simpleMember" => array:2 [
      "type" => "string"
      "description" => "Short description of the member"
    ]
    "stringArray" => array:2 [
      "type" => "array"
      "items" => array:1 [
        "type" => "string"
      ]
    ]
    "objectArray" => array:2 [
      "type" => "array"
      "items" => array:3 [
        "type" => "object"
        "properties" => array:2 [
          "attr1" => array:1 [
            "type" => "string"
          ]
          "attr2" => array:1 [
            "type" => "string"
          ]
        ]
        "required" => array:2 [
          0 => "attr1"
          1 => "attr2"
        ]
      ]
    ]
    "optionalMember" => array:1 [
      "type" => "string"
    ]
    "viaSetter" => array:1 [
      "type" => "string"
    ]
  ]
  "required" => array:4 [
    0 => "simpleMember"
    1 => "stringArray"
    2 => "objectArray"
    3 => "viaSetter"
  ]
]
```

### Tool calling

```php
$res = $this->llm->generate(
  $messages,
  toolbox: (new Toolbox(
    new Tool(
      'toolName',
      'Tool description',
      function (
        #[Description('Short description with example values')] string $simpleParameter,
        #[ArrayType('string')] array $stringArray,
        string $optionalParameter = 'defaultValue'
      ) {
        ...
      }
    )
  ))->setMaxLLMCalls(10) // Maximal number of LLM calls, set to a sensible value to avoid infinite loops or expensive calls
)
```

Internally generated schema:

```php
array:3 [
  "type" => "object"
  "properties" => array:3 [
    "simpleParameter" => array:2 [
      "type" => "string"
      "description" => "Short description with example values"
    ]
    "stringArray" => array:2 [
      "type" => "array"
      "items" => array:1 [
        "type" => "string"
      ]
    ]
    "optionalParameter" => array:1 [
      "type" => "string"
    ]
  ]
  "required" => array:2 [
    0 => "simpleParameter"
    1 => "stringArray"
  ]
]
```
