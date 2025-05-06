<?php

namespace AiBundle\Prompting\Tools;

abstract readonly class AbstractTool {

  public function __construct(
    public string $name,
    public string $description
  ) {}

}
