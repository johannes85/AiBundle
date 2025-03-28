<?php

namespace AiBundle\LLM\OpenAi\Dto;

use AiBundle\Prompting\FileType;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use InvalidArgumentException;

class OpenAiMessage {

  /**
   * @param string $role
   * @param string|array<ContentPart> $content
   */
  public function __construct(
    public readonly string $role,
    public readonly string|array $content
  ) {}

  /**
   * Creates new OpenAi message from message
   *
   * @param Message $message
   * @return self
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
        MessageRole::SYSTEM => 'developer',
        MessageRole::AI => 'assistant',
        /** @phpstan-ignore match.alwaysTrue */
        MessageRole::HUMAN => 'user',
        default => throw new InvalidArgumentException(
          'OpenAi message doesn\'t support type: ' . $message->role->name
        )
      },
      $content
    );
  }

}
