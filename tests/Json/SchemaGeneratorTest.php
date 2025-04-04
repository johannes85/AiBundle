<?php

namespace AiBundle\Tests\Json;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Json\SchemaGenerator;
use AiBundle\Tests\Mock\TestClass1;
use PHPUnit\Framework\TestCase;

class SchemaGeneratorTest extends TestCase {

  public function test_generateForClosureParameters() {
    $generator = new SchemaGenerator();
    $schema = $generator->generateForClosureParameters(function (
      TestClass1 $param1,
      int $param2,
      #[ArrayType('number')] ?array $param3 = null
    ) {});
    $this->assertEquals(
      [
        'type' => 'object',
        'properties' => [
          'param1' => TestClass1::SCHEMA,
          'param2' => ['type' => 'integer'],
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
