<?php

namespace AiBundle\Tests\Json;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Json\SchemaGenerator;
use AiBundle\Tests\Mock\TestClass1;
use PHPUnit\Framework\TestCase;

class SchemaGeneratorTest extends TestCase {

  public function test_generateForClosureParameters() {
    $generator = new SchemaGenerator();
    $schemas = $generator->generateForClosureParameters(function (
      TestClass1 $param1,
      int $param2,
      #[ArrayType('number')] array $param3
    ) {});
    $this->assertEquals(TestClass1::SCHEMA, $schemas['param1']);
    $this->assertEquals(['type' => 'integer'], $schemas['param2']);
    $this->assertEquals(['type' => 'array', 'items' => ['type' => 'number']], $schemas['param3']);
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
