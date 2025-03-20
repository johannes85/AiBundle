<?php

namespace AiBundle\Tests\Json;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Tests\Mock\TestClass1;
use PHPUnit\Framework\TestCase;

class SchemaGeneratorTest extends TestCase {

  public function test_generateForClass() {
    $generator = new SchemaGenerator();
    $schema = $generator->generateForClass(TestClass1::class);
    $this->assertEquals(
      [
        'type' => 'object',
        'properties' => [
          'pString' => ['type' => 'string'],
          'pNullableString' => ['type' => 'string'],
          'pNullableString2' => ['type' => 'string'],
          'pNUll' => ['type' => 'null'],
          'pInt' => ['type' => 'integer'],
          'pFloat' => ['type' => 'number'],
          'pBool' => ['type' => 'boolean'],
          'pArray' => ['type' => 'array'],
          'pClass' => [
            'type' => 'object',
            'properties' => [
              'member1' => [
                'type' => 'string',
              ],
              'member2' => [
                'type' => 'string',
              ],
            ],
            'required' => ['member2'],
          ],
          'pArrayItemTypes1' => [
            'type' => 'array',
            'items' => ['type' => 'string'],
          ],
          'string1' => [
            'type' => 'string',
          ],
          'arrayItemTypes2' => [
            'type' => 'array',
            'items' => ['type' => 'number'],
          ]
        ],
        'required' => [
          'pString',
          'pInt',
          'pFloat',
          'pBool',
          'pArray',
          'pClass',
          'pArrayItemTypes1',
          'string1',
          'arrayItemTypes2'
        ],
      ],
      $schema
    );
  }

}
