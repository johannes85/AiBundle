<?php

namespace AiBundle\Json;

use AiBundle\Json\Attributes\ArrayType;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class SchemaGenerator {

  /**
   * Generates schema for a class
   *
   * @param string $className
   * @return array<mixed>
   * @throws SchemaGeneratorException
   */
  public function generateForClass(string $className): array {
    try {
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
        if (($res = $this->generateForMethod($method)) === null) {
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
      return $schema;
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
          if (
            !empty($attribute = $property->getAttributes(ArrayType::class)) &&
            ($arrayItemType = $attribute[0]->newInstance()->itemType) !== null
          ) {
            $value['items'] = ['type' => $arrayItemType];
          }
        }
      } else {
        $value = $this->generateForClass($propertyType->getName());
      }
      $required = !$propertyType->allowsNull();
    }
    return [
      'name' => $property->getName(),
      'value' => $value,
      'required' => $required,
    ];
  }

  /**
   * Generates schema for a method
   *
   * @param ReflectionMethod $method
   * @return array<mixed>|null
   */
  private function generateForMethod(ReflectionMethod $method): ?array {
    if (!str_starts_with($name = $method->getName(), 'set')) {
      return null;
    }
    $params = $method->getParameters();
    if (empty($params)) {
      return null;
    }
    $name = lcfirst(substr($name, 3));
    $parameterType = $params[0]->getType();
    $value = ['type' => 'mixed'];
    $required = false;
    if ($parameterType instanceof ReflectionNamedType) {
      $parameterTypeName = $this->propertyTypeForString($parameterType->getName());
      $value['type'] = $parameterTypeName;
      if ($parameterTypeName === 'array') {
        if (
          !empty($attribute = $params[0]->getAttributes(ArrayType::class)) &&
          ($arrayItemType = $attribute[0]->newInstance()->itemType) !== null
        ) {
          $value['items'] = ['type' => $arrayItemType];
        }
      }
      $required = !$parameterType->allowsNull();
    }
    return [
      'name' => $name,
      'value' => $value,
      'required' => $required,
    ];
  }

}
