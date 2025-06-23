<?php

namespace AiBundle\Tests\Mock;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Json\Attributes\Description;

class TestClass3 {

  public function test(
    TestClass1 $param1,
    #[Description('param2 description')] int $param2,
    #[ArrayType('number')] ?array $param3 = null
  ): void {

  }


}
