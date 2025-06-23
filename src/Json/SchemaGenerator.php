<?php

namespace AiBundle\Json;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Json\Attributes\Description;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

class SchemaGenerator {

  /**
   * Generates json schema for parameters of a function or closure
   *
   * @param Closure|ReflectionFunctionAbstract $function
   * @return array<mixed>
   * @throws ReflectionException
   * @throws SchemaGeneratorException
   */
  public function generateForFunctionParameters(
    Closure|ReflectionFunctionAbstract $function
  ): array {
    $schema = [
      'type' => 'object',
      'properties' => [],
    ];
    $requiredProperties = [];

    $parameters = $function instanceof ReflectionFunctionAbstract
      ? $function->getParameters()
      : (new ReflectionFunction($function))->getParameters();
    foreach($parameters as $parameter) {
      $res = $this->generateForParameter($parameter);
      $schema['properties'][$parameter->getName()] = $res['schema'];
      if ($res['required']) {
        $requiredProperties[] = $parameter->getName();
      }
    }
    if (!empty($requiredProperties)) {
      $schema['required'] = $requiredProperties;
    }
    return $schema;
  }

  /**
   * Generates json schema for a class
   *
   * @param string $className
   * @return array<mixed>
   * @throws SchemaGeneratorException
   */
  public function generateForClass(string $className): array {
    try {
      if ($isArray = str_ends_with($className, '[]')) {
        $className = substr($className, 0, -2);
      }
      $reflectionClass = new ReflectionClass($className);
      $schema = [
        'type' => 'object',
        'properties' => [],
      ];
      $requiredProperties = [];
      foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
        $res = $this->generateForProperty($property);
        $schema['properties'][$res['name']] = $res['value'];
        if ($res['required']) {
          $requiredProperties[] = $res['name'];
        }
      }
      foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!str_starts_with($method->getName(), 'set')) {
          continue;
        }
        if (($res = $this->generateForSetterMethod($method)) === null) {
          continue;
        }
        $schema['properties'][$res['name']] = $res['value'];
        if ($res['required']) {
          $requiredProperties[] = $res['name'];
        }
      }
      if (!empty($requiredProperties)) {
        $schema['required'] = $requiredProperties;
      }
      return $isArray
        ? ['type' => 'array', 'items' => $schema]
        : $schema;
    } catch (ReflectionException $ex) {
      throw new SchemaGeneratorException(
        'Error getting info about class provided: '.$ex->getMessage(),
        previous: $ex
      );
    }
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
   * Generates schema for a property
   *
   * @param ReflectionProperty $property
   * @return array<mixed>
   * @throws SchemaGeneratorException
   */
  private function generateForProperty(ReflectionProperty $property): array {
    $propertyType = $property->getType();
    $value = ['type' => 'mixed'];
    $required = false;
    if ($propertyType instanceof ReflectionNamedType) {
      if ($propertyType->isBuiltin()) {
        $propertyTypeName = $this->propertyTypeForString($propertyType->getName());
        $value['type'] = $propertyTypeName;
        if ($propertyTypeName === 'array') {
          if (!empty($attributes = $property->getAttributes(ArrayType::class))) {
            $instance = $attributes[0]->newInstance();
            if ($instance->itemType !== null) {
              $value['items'] = ['type' => $instance->itemType];
            } elseif ($instance->itemClass !== null) {
              $value['items']= $this->generateForClass($instance->itemClass);
            }
          }
        }
      } else {
        $value = $this->generateForClass($propertyType->getName());
      }
      $required = !$propertyType->allowsNull();
    }
    if (!empty($attributes = $property->getAttributes(Description::class))) {
      $value['description'] = $attributes[0]->newInstance()->description;
    }
    return [
      'name' => $property->getName(),
      'value' => $value,
      'required' => $required,
    ];
  }

  /**
   * Generates schema for a setter method
   *
   * @param ReflectionMethod $method
   * @return array<mixed>|null
   * @throws SchemaGeneratorException
   */
  private function generateForSetterMethod(ReflectionMethod $method): ?array {
    if (empty($params = $method->getParameters())) {
      return null;
    }
    $parameterSchema = $this->generateForParameter($params[0]);
    return [
      'name' => lcfirst(substr($method->getName(), 3)),
      'value' => $parameterSchema['schema'],
      'required' => $parameterSchema['required'],
    ];
  }

  /**
   * Generates schema for parameter
   *
   * @param ReflectionParameter $parameter
   * @return array<mixed>
   * @throws SchemaGeneratorException
   */
  private function generateForParameter(ReflectionParameter $parameter): array {
    $type = $parameter->getType();
    $schema = ['type' => 'mixed'];
    $required = false;
    if ($type instanceof ReflectionNamedType) {
      if ($type->isBuiltin()) {
        $schema['type'] = $this->propertyTypeForString($type->getName());
        if ($schema['type'] === 'array') {
          if (!empty($attributes = $parameter->getAttributes(ArrayType::class))) {
            $instance = $attributes[0]->newInstance();
            if ($instance->itemType !== null) {
              $schema['items'] = ['type' => $instance->itemType];
            } elseif ($instance->itemClass !== null) {
              $schema['items'] = $this->generateForClass($instance->itemClass);
            }
          }
        }
      } else {
        $schema = $this->generateForClass($type->getName());
      }
      $required = !$parameter->isDefaultValueAvailable();
    }
    if (!empty($attributes = $parameter->getAttributes(Description::class))) {
      $schema['description'] = $attributes[0]->newInstance()->description;
    }
    return [
      'schema' => $schema,
      'required' => $required,
    ];
  }

}
