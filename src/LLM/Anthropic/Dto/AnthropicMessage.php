<?php

namespace AiBundle\LLM\Anthropic\Dto;

use AiBundle\LLM\GoogleAi\Dto\Content;
use AiBundle\LLM\Ollama\Dto\OllamaMessage;
use AiBundle\Prompting\FileType;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use InvalidArgumentException;

class AnthropicMessage {

  /**
   * AnthropicMessage constructor
   *
   * @param string $role
   * @param string|array<ContentBlock> $content
   */
  public function __construct(
    public readonly string $role,
    public readonly string|array $content
  ) {}

  /**
   * Creates new AnthropicMessage from message
   *
   * @param Message $message
   * @return self
   */
  public static function fromMessage(Message $message): self {
    $content = [];
    foreach ($message->files as $file) {
      /** @phpstan-ignore notIdentical.alwaysFalse */
      if ($file->type !== FileType::IMAGE) {
        continue;
      }
      $content[] = (new ContentBlock())->setType(ContentBlockType::IMAGE)->setSource(
        Source::fromBase64Data($file->mimeType, $file->getBase64Content())
      );
    }
    $content[] = (new ContentBlock())->setType(ContentBlockType::TEXT)->setText($message->content);

    return new self(
      match ($message->role) {
        MessageRole::AI => 'assistant',
        MessageRole::HUMAN => 'user',
        default => throw new InvalidArgumentException(
          'Anthropic message doesn\'t support type: ' . $message->role->name
        )
      },
      $content
    );
  }

}
