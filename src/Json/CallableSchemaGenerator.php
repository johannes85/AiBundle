<?php

namespace AiBundle\Json;

use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class CallableSchemaGenerator {

  /**
   * @param callable|string $callable
   * @return array<mixed>
   * @throws ReflectionException
   */
  public function generateForCallable(callable|string $callable): array {
    $schema = [
      'type' => 'object',
      'properties' => []
    ];

    $parameters = match (true) {
      $callable instanceof Closure
        => (new ReflectionFunction($callable))->getParameters(),
      is_string($callable) && function_exists($callable)
        => (new ReflectionFunction($callable))->getParameters(),
      is_object($callable) && method_exists($callable, '__invoke')
        => (new ReflectionMethod($callable, '__invoke'))->getParameters(),
      is_array($callable)
        => (new ReflectionMethod($callable[0], $callable[1]))->getParameters(),
      default => throw new \InvalidArgumentException('Callable provided isn\'t supported.')
    };
    foreach ($parameters as $parameter) {
      $schema['properties'][$parameter->getName()] = $this->generateForParameter($parameter);
    }

    return $schema;
  }

  /**
   * Returns property type for a string
   *
   * @param string $type
   * @return string
   */
  private function propertyTypeForString(string $type): string {
    return  match ($type) {
      'array' => 'array',
      'bool' => 'boolean',
      'null' => 'null',
      'int' => 'integer',
      'float' => 'number',
      'string' => 'string',
      default => 'mixed',
    };
  }

  /**
   * Generates schema for parameter
   *
   * @param ReflectionParameter $parameter
   * @return array<mixed>
   */
  private function generateForParameter(ReflectionParameter $parameter): array {
    $parameterType = $parameter->getType();
    $type = 'mixed';
    $required = false;
    if ($parameterType instanceof ReflectionNamedType) {
      if ($parameterType->isBuiltin()) {
        $type = $this->propertyTypeForString($parameterType->getName());
      }
      $required = !$parameterType->allowsNull();
    }
    return [
      'type' => $type,
      'required' => $required,
    ];
  }

}
