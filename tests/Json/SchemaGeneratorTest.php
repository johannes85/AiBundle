<?php

namespace AiBundle\Tests\Json;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Json\Attributes\Description;
use AiBundle\Json\SchemaGenerator;
use AiBundle\Tests\Mock\TestClass1;
use AiBundle\Tests\Mock\TestClass2;
use AiBundle\Tests\Mock\TestClass3;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;

function test (
  TestClass1 $param1,
  #[Description('param2 description')] int $param2,
  #[ArrayType('number')] ?array $param3 = null
) {}

class SchemaGeneratorTest extends TestCase {

  public function test_generateForFunctionParameters_closure() {
    $generator = new SchemaGenerator();
    $schema = $generator->generateForFunctionParameters(function (
      TestClass1 $param1,
      #[Description('param2 description')] int $param2,
      #[ArrayType('number')] ?array $param3 = null
    ) {});
    $this->assertEquals(
      [
        'type' => 'object',
        'properties' => [
          'param1' => TestClass1::SCHEMA,
          'param2' => ['type' => 'integer', 'description' => 'param2 description'],
          'param3' => ['type' => 'array', 'items' => ['type' => 'number']],
        ],
        'required' => ['param1', 'param2']
      ],
      $schema
    );
  }

  public function test_generateForFunctionParameters_method(): void {
    $generator = new SchemaGenerator();
    $schema = $generator->generateForFunctionParameters(
      new ReflectionMethod(TestClass3::class, 'test')
    );
    $this->assertEquals(
      [
        'type' => 'object',
        'properties' => [
          'param1' => TestClass1::SCHEMA,
          'param2' => ['type' => 'integer', 'description' => 'param2 description'],
          'param3' => ['type' => 'array', 'items' => ['type' => 'number']],
        ],
        'required' => ['param1', 'param2']
      ],
      $schema
    );
  }

  public function test_generateForFunctionParameters_function(): void {
    $generator = new SchemaGenerator();
    $schema = $generator->generateForFunctionParameters(new ReflectionFunction(function (
      TestClass1 $param1,
      #[Description('param2 description')] int $param2,
      #[ArrayType('number')] ?array $param3 = null
    ) {}));
    $this->assertEquals(
      [
        'type' => 'object',
        'properties' => [
          'param1' => TestClass1::SCHEMA,
          'param2' => ['type' => 'integer', 'description' => 'param2 description'],
          'param3' => ['type' => 'array', 'items' => ['type' => 'number']],
        ],
        'required' => ['param1', 'param2']
      ],
      $schema
    );
  }

  public function test_generateForClass() {
    $generator = new SchemaGenerator();
    $schema = $generator->generateForClass(TestClass1::class);
    $this->assertEquals(
      TestClass1::SCHEMA,
      $schema
    );
  }

  public function test_generateForArrayOfClass() {
    $generator = new SchemaGenerator();
    $schema = $generator->generateForClass(TestClass1::class.'[]');
    $this->assertEquals(
      ['type' => 'array', 'items' => TestClass1::SCHEMA],
      $schema
    );
  }

}
