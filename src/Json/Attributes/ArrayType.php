<?php

namespace AiBundle\Json\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class ArrayType {

  public function __construct(
    public ?string $itemType = null,
    public ?string $itemClass = null
  ) {}

}
