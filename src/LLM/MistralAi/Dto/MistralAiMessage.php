<?php

namespace AiBundle\LLM\MistralAi\Dto;

use AiBundle\LLM\LLMCapabilityException;
use AiBundle\Prompting\FileType;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class MistralAiMessage {

  /**
   * @param string $role
   * @param string|array<ContentPart> $content
   * @param string|null $name
   * @param string|null $toolCallId
   * @param array<ToolCall>|null $toolCalls
   */
  public function __construct(
    public string $role,
    public string|array $content,
    public ?string $name = null,
    #[SerializedName('tool_call_id')] public ?string $toolCallId = null,
    #[SerializedName('tool_calls')] public ?array $toolCalls = null
  ) {}

  /**
   * Creates new MistralAiMessage from message
   *
   * @param Message $message
   * @return self
   * @throws LLMCapabilityException
   */
  public static function fromMessage(Message $message): self {
    $content = [
      ContentPart::forText($message->content)
    ];
    foreach ($message->files as $file) {
      /** @phpstan-ignore notIdentical.alwaysFalse */
      if ($file->type !== FileType::IMAGE) {
        continue;
      }
      $content[] = ContentPart::forBase64EncodedImage($file->mimeType, $file->getBase64Content());
    }

    return new self(
      match ($message->role) {
        MessageRole::SYSTEM => 'system',
        MessageRole::AI => 'assistant',
        /** @phpstan-ignore match.alwaysTrue */
        MessageRole::HUMAN => 'user',
        default => throw new LLMCapabilityException(
          'OpenAi message doesn\'t support type: ' . $message->role->name
        )
      },
      $content
    );
  }

}
