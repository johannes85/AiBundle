<?php

namespace AiBundle\Json\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
readonly class Description {

  public function __construct(
    public string $description
  ) {}

}
