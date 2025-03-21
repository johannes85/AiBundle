<?php

namespace AiBundle\LLM\Anthropic\Dto;

class ContentBlock {

  public ContentBlockType|null $type = null;
  public ?string $text = null;

  /** @var array<mixed>|null  */
  public ?array $input = null;
  public ?string $id = null;

  /** @var array<mixed>|null */
  public ?array $citations = null;
  public ?string $signature = null;
  public ?string $thinking = null;
  public ?string $data = null;

}
