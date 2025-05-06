<?php

namespace tests;

use AiBundle\Serializer\EmptyObjectHelper;
use ArrayObject;
use PHPUnit\Framework\TestCase;

class EmptyObjectHelperTest extends TestCase {

  public function test_injectEmptyObjects_definedPaths() {
    $this->assertEquals(
      [
        'type' => 'function',
        'realArray' => [],
        'function' => [
          'name' => 'function_name',
          'description' => 'function_description',
          'parameters' => [
            'type' => 'object',
            'properties' => new ArrayObject(),
            'required' => ['key1'],
          ],
        ],
      ],
      EmptyObjectHelper::injectEmptyObjects([
        'type' => 'function',
        'realArray' => [],
        'function' => [
          'name' => 'function_name',
          'description' => 'function_description',
          'parameters' => [
            'type' => 'object',
            'properties' => [],
            'required' => ['key1'],
          ],
        ],
      ], ['parameters/properties'])
    );
  }

  public function test_injectEmptyObjects_always() {
    $this->assertEquals(
      [
        'type' => 'function',
        'realArray' => new ArrayObject(),
        'function' => [
          'name' => 'function_name',
          'description' => 'function_description',
          'parameters' => [
            'type' => 'object',
            'properties' => new ArrayObject(),
            'required' => ['key1'],
          ],
        ],
      ],
      EmptyObjectHelper::injectEmptyObjects([
        'type' => 'function',
        'realArray' => [],
        'function' => [
          'name' => 'function_name',
          'description' => 'function_description',
          'parameters' => [
            'type' => 'object',
            'properties' => [],
            'required' => ['key1'],
          ],
        ],
      ])
    );
  }

}
