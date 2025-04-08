<?php

namespace AiBundle\LLM\MistralAi\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ContentPart {

  private ?string $text = null;
  #[SerializedName('image_url')] private ?ImageUrl $imageUrl = null;
  #[SerializedName('document_url')] private ?string $documentUrl = null;
  #[SerializedName('document_name')] private ?string $documentName = null;

  /** @var array<int>|null */
  #[SerializedName('reference_ids')] private ?array $referenceIds = null;

  public function __construct(
    public readonly ContentPartType $type
  ) {}

  /**
   * Creates new ContentBlock for image URL
   *
   * @param string $imageUrl
   * @param string|null $detailLevel
   * @return self
   */
  public static function forImageUrl(string $imageUrl, ?string $detailLevel = null): self {
    return (new self(ContentPartType::IMAGE_URL))
      ->setImageUrl(new ImageUrl($imageUrl, $detailLevel));
  }

  /**
   * Creates new ContentBlock for base64 encoded image
   *
   * @param string $mimeType
   * @param string $data
   * @param string|null $detailLevel
   * @return self
   */
  public static function forBase64EncodedImage(
    string $mimeType,
    string $data, ?string $detailLevel = null
  ): self {
    return (new self(ContentPartType::IMAGE_URL))
      ->setImageUrl(new ImageUrl('data:'.$mimeType.';base64,' . $data, $detailLevel));
  }

  /**
   * Creates new ContentBlock for text
   *
   * @param string $text
   * @return self
   */
  public static function forText(string $text): self {
    return (new self(ContentPartType::TEXT))
      ->setText($text);
  }

  public function getText(): ?string {
    return $this->text;
  }

  public function setText(?string $text): static {
    $this->text = $text;
    return $this;
  }

  public function getImageUrl(): ?ImageUrl {
    return $this->imageUrl;
  }

  public function setImageUrl(?ImageUrl $imageUrl): static {
    $this->imageUrl = $imageUrl;
    return $this;
  }

  public function getDocumentUrl(): ?string {
    return $this->documentUrl;
  }

  public function setDocumentUrl(?string $documentUrl): static {
    $this->documentUrl = $documentUrl;
    return $this;
  }

  public function getDocumentName(): ?string {
    return $this->documentName;
  }

  public function setDocumentName(?string $documentName): static {
    $this->documentName = $documentName;
    return $this;
  }

  /**
   * @return array<int>|null
   */
  public function getReferenceIds(): ?array {
    return $this->referenceIds;
  }

  /**
   * @param array<int>|null $referenceIds
   * @return $this
   */
  public function setReferenceIds(?array $referenceIds): static {
    $this->referenceIds = $referenceIds;
    return $this;
  }

}
