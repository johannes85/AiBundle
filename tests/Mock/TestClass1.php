<?php

namespace AiBundle\Tests\Mock;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Json\Attributes\Description;
use AiBundle\Tests\Mock\SubNamespace\TestClass2;

class TestClass1 {

  public const SCHEMA = [
    'type' => 'object',
    'properties' => [
      'pString' => ['type' => 'string', 'description' => 'pString description'],
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
      'pArrayItemTypes3' => [
        'type' => 'array',
        'items' => [
          'type' => 'object',
          'properties' => [
            'member1' => [
              'type' => 'string',
            ],
            'member2' => [
              'type' => 'string',
            ],
          ],
          'required' => ['member2']
        ],
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
      'pArrayItemTypes3',
      'string1',
      'arrayItemTypes2'
    ],
  ];

  #[Description('pString description')]
  public string $pString;

  public ?string $pNullableString;
  public string|null $pNullableString2;

  public null $pNUll;

  public int $pInt;
  public float $pFloat;

  public bool $pBool;

  public array $pArray;

  private string $privString;

  public function setString1(string $string): void {}

  public TestClass2 $pClass;

  #[ArrayType(itemType: 'string')]
  public array $pArrayItemTypes1;

  public function setArrayItemTypes2(#[ArrayType(itemType: 'number')] array $array): void {}

  #[ArrayType(itemClass: TestClass2::class)]
  public array $pArrayItemTypes3;

}
