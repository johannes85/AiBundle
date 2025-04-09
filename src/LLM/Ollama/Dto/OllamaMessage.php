<?php

namespace AiBundle\LLM\Ollama\Dto;

use AiBundle\Prompting\File;
use AiBundle\Prompting\FileType;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class OllamaMessage {

  /**
   * @param string $role
   * @param string $content
   * @param array<string> $images
   * @param array<ToolCall>|null $toolCalls
   */
  public function __construct(
    public string $role,
    public string $content,
    public array $images = [],
    #[SerializedName('tool_calls')] public ?array $toolCalls = null,
    public ?string $name = null
  ) {}

  /**
   * Creates new Ollama messsage from message
   *
   * @param Message $message
   * @return OllamaMessage
   */
  public static function fromMessage(Message $message): OllamaMessage {
    return new self(
      match ($message->role) {
        MessageRole::AI => 'assistant',
        MessageRole::HUMAN => 'user',
        MessageRole::SYSTEM => 'system'
      },
      $message->content,

      array_map(
        fn (File $file) => $file->getBase64Content(),
        /** @phpstan-ignore identical.alwaysTrue */
        array_filter($message->files, fn (File $file) => $file->type === FileType::IMAGE)
      )
    );
  }

  /**
   * Creates message from Ollama message
   *
   * @return Message
   */
  public function toMessage(): Message {
    return (new Message(
      match ($this->role) {
        'assistant' => MessageRole::AI,
        'user' => MessageRole::HUMAN,
        'system' => MessageRole::SYSTEM,
        default => throw new InvalidArgumentException('Invalid role of Ollama message' . $this->role)
      },
      $this->content
    ));
  }

}
