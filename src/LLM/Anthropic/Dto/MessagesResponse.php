<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class MessagesResponse {

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
    public readonly array $content,
    public readonly string $id,
    public readonly string $model,
    public readonly string $role,
    #[SerializedName('stop_reason')] public readonly string $stopReason,
    #[SerializedName('stop_sequence')] public readonly ?string $stopSequence,
    public readonly string $type,
    public readonly array $usage,
  ) {}

}
