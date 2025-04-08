<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class MessagesResponse {

  /**
   * @param array<ContentBlock> $content
   * @param string $id
   * @param string $model
   * @param string $role
   * @param string $stopReason
   * @param ?string $stopSequence
   * @param string $type
   * @param array<mixed> $usage
   */
  public function __construct(
    public array $content,
    public string $id,
    public string $model,
    public string $role,
    #[SerializedName('stop_reason')] public string $stopReason,
    #[SerializedName('stop_sequence')] public ?string $stopSequence,
    public string $type,
    public array $usage,
  ) {}

}
