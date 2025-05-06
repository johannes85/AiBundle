<?php

namespace AiBundle\Prompting\Tools;

use Closure;

readonly class Tool {

  public function __construct(
    public string $name,
    public string $description,
    public Closure $callback
  ) {}

}
