<?php

namespace AiBundle\Serializer;

use ArrayObject;


class EmptyObjectHelper {

  /**
   * Sets empty arrays in the given data to empty objects.
   *
   * @param mixed $data
   * @param array<string>|null $forObjectTypePaths If set, only the paths matching these will be converted to empty objects.
   * @param string $currentPath
   * @return mixed
   */
  public static function injectEmptyObjects(
    mixed $data,
    array|null $forObjectTypePaths = null,
    string $currentPath = ''
  ): mixed {
    if (is_array($data)) {
      if (count($data) === 0) {
        $ret = [];
        if ($forObjectTypePaths === null) {
          $ret = new ArrayObject();
        } else {
          foreach ($forObjectTypePaths as $path) {
            if (str_ends_with($currentPath, $path)) {
              $ret = new ArrayObject();
              break;
            }
          }
        }
      } else {
        $ret = [];
        foreach ($data as $key => $value) {
          $ret[$key] = self::injectEmptyObjects($value, $forObjectTypePaths, $currentPath . '/' . $key);
        }
      }
    } else {
      $ret = $data;
    }
    return $ret;
  }

}
