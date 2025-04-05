<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class ContentBlock {

  private ContentBlockType|null $type = null;
  private ?string $text = null;
  private ?Source $source = null;

  private ?string $name = null;

  #[SerializedName('tool_use_id')] private ?string $toolUseId = null;

  private ?string $content = null;

  /** @var array<mixed>|null  */
  private ?array $input = null;
  private ?string $id = null;

  /** @var array<mixed>|null */
  private ?array $citations = null;
  private ?string $signature = null;
  private ?string $thinking = null;
  private ?string $data = null;

  /**
   * @return ContentBlockType|null
   */
  public function getType(): ?ContentBlockType {
    return $this->type;
  }

  /**
   * @param ContentBlockType|null $type
   * @return static
   */
  public function setType(?ContentBlockType $type): static {
    $this->type = $type;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getText(): ?string {
    return $this->text;
  }

  /**
   * @param string|null $text
   * @return static
   */
  public function setText(?string $text): static {
    $this->text = $text;
    return $this;
  }

  /**
   * @return Source|null
   */
  public function getSource(): ?Source {
    return $this->source;
  }

  /**
   * @param Source|null $source
   * @return static
   */
  public function setSource(?Source $source): static {
    $this->source = $source;
    return $this;
  }

  public function getName(): ?string {
    return $this->name;
  }

  public function setName(?string $name): ContentBlock {
    $this->name = $name;
    return $this;
  }

  public function getToolUseId(): ?string {
    return $this->toolUseId;
  }

  public function setToolUseId(?string $toolUseId): ContentBlock {
    $this->toolUseId = $toolUseId;
    return $this;
  }

  public function getContent(): ?string {
    return $this->content;
  }

  public function setContent(?string $content): ContentBlock {
    $this->content = $content;
    return $this;
  }

  /**
   * @return array<mixed>|null
   */
  public function getInput(): ?array {
    return $this->input;
  }

  /**
   * @param array<mixed>|null $input
   * @return static
   */
  public function setInput(?array $input): static {
    $this->input = $input;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getId(): ?string {
    return $this->id;
  }

  /**
   * @param string|null $id
   * @return static
   */
  public function setId(?string $id): static {
    $this->id = $id;
    return $this;
  }

  /**
   * @return array<mixed>|null
   */
  public function getCitations(): ?array {
    return $this->citations;
  }

  /**
   * @param array<mixed>|null $citations
   * @return static
   */
  public function setCitations(?array $citations): static {
    $this->citations = $citations;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getSignature(): ?string {
    return $this->signature;
  }

  /**
   * @param string|null $signature
   * @return static
   */
  public function setSignature(?string $signature): static {
    $this->signature = $signature;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getThinking(): ?string {
    return $this->thinking;
  }

  /**
   * @param string|null $thinking
   * @return static
   */
  public function setThinking(?string $thinking): static {
    $this->thinking = $thinking;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getData(): ?string {
    return $this->data;
  }

  /**
   * @param string|null $data
   * @return static
   */
  public function setData(?string $data): static {
    $this->data = $data;
    return $this;
  }

}
