<?php

namespace AiBundle\LLM\OpenAi\Dto;

use Svg\Tag\Image;
use Symfony\Component\Serializer\Annotation\SerializedName;

class ContentPart {

  private ?string $text = null;
  #[SerializedName('image_url')] private ?ImageUrl $imageUrl = null;

  public function __construct(
    private ContentPartType $type
  ) {}

  /**
   * Creates new ContentBlock for image URL
   *
   * @param string $imageUrl
   * @param DetailLevel|null $detailLevel
   * @return self
   */
  public static function forImageUrl(string $imageUrl, ?DetailLevel $detailLevel = null): self {
    return (new self(ContentPartType::IMAGE_URL))
      ->setImageUrl(new ImageUrl($imageUrl, $detailLevel));
  }

  /**
   * Creates new ContentBlock for base64 encoded image
   *
   * @param string $mimeType
   * @param string $data
   * @param DetailLevel|null $detailLevel
   * @return self
   */
  public static function forBase64EncodedImage(
    string $mimeType,
    string $data, ?DetailLevel $detailLevel = null
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

  public function getType(): ContentPartType {
    return $this->type;
  }

  public function setType(ContentPartType $type): static {
    $this->type = $type;
    return $this;
  }

}
