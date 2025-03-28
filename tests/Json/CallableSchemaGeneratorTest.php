<?php

namespace AiBundle\Tests\Json;

use AiBundle\Json\CallableSchemaGenerator;
use PHPUnit\Framework\TestCase;

function test_function(
  string $string,
  ?string $optionalString
) {}

class CallableSchemaGeneratorTest extends TestCase {

  public function foo(
    string $string,
    ?string $optionalString
  ) {}

  public function test_closure(): void {

    $closure = function (
      string $string,
      ?string $optionalString
    ) {};

    $schema = (new CallableSchemaGenerator())->generateForCallable($closure);

  }

  public function test_function(): void {

    $schema = (new CallableSchemaGenerator())->generateForCallable('AiBundle\Tests\Json\test_function');

  }

  public function test_invokable(): void {

    $class = new class() {
      public function __invoke(
        string $string,
        ?string $optionalString
      ) {}
    };

    $schema = (new CallableSchemaGenerator())->generateForCallable($class);

  }

  public function test_object(): void {

    $class = new class() {
      public function test_function(
        string $string,
        ?string $optionalString
      ) {}
    };

    $schema = (new CallableSchemaGenerator())->generateForCallable([$class, 'test_function']);

  }

}
