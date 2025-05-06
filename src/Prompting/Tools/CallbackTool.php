<?php

namespace AiBundle\Prompting\Tools;

use Closure;

readonly class CallbackTool extends AbstractTool {

  public function __construct(
    string $name,
    string $description,
    public Closure $callback
  ) {
    parent::__construct($name, $description);
  }

}
