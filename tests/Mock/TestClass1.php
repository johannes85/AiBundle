<?php

namespace AiBundle\Tests\Mock;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Tests\Mock\SubNamespace\TestClass2;

class TestClass1 {
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

}
