<?php

namespace AiBundle\Json;

class SchemaManipulator {

  /**
   * Removes properties from a JSON schema based on the provided property paths.
   *
   * @param array<mixed> $schema
   * @param array<string> $removePropertyPaths
   * @param string $currentPath
   * @return array<mixed>
   */
  public static function removeProperties(
    array $schema,
    array $removePropertyPaths,
    string $currentPath = ''
  ): array {
    $ret = [];
    foreach ($schema as $propertyName => $value) {
      $propertyPath = $currentPath . '/' . $propertyName;
      foreach ($removePropertyPaths as $path) {
        if (str_ends_with($propertyPath, '/' . $path)) {
          continue 2;
        }
      }
      $ret[$propertyName] = is_array($value)
        ? self::removeProperties($value, $removePropertyPaths, $propertyPath)
        : $value;
    }
    return $ret;
  }

}
