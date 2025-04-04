<?php

namespace AiBundle\Prompting\Tools;

use Closure;

class Tool {

  public function __construct(
    public readonly string $name,
    public readonly string $description,
    public readonly Closure $callback,
    public readonly bool $autoBackfeed = false
  ) {}

}
