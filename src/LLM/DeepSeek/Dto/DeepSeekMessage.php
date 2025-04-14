<?php

namespace AiBundle\LLM\DeepSeek\Dto;

use AiBundle\LLM\LLMCapabilityException;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use Symfony\Component\Serializer\Attribute\SerializedName;

class DeepSeekMessage {

  /**
   * @param string $role
   * @param string|null $content
   * @param string|null $name
   * @param string|null $toolCallId
   * @param array<ToolCall>|null $toolCalls
   */
  public function __construct(
    public string $role,
    public ?string $content,
    public ?string $name = null,
    #[SerializedName('tool_call_id')] public ?string $toolCallId = null,
    #[SerializedName('tool_calls')] public ?array $toolCalls = null
  ) {}

  /**
   * Creates new DeepSeek message from message
   *
   * @param Message $message
   * @return self
   * @throws LLMCapabilityException
   */
  public static function fromMessage(Message $message): self {
    if (!empty($message->files)) {
      throw new LLMCapabilityException('Processing of files is not supported by this LLM');
    }
    return new self(
      match ($message->role) {
        MessageRole::SYSTEM => 'system',
        MessageRole::AI => 'assistant',
        /** @phpstan-ignore match.alwaysTrue */
        MessageRole::HUMAN => 'user',
        default => throw new LLMCapabilityException(
          'Deepseek message doesn\'t support type: ' . $message->role->name
        )
      },
      $message->content
    );
  }

}
