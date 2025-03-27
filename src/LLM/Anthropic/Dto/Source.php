<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

class Source {

  public function __construct(
    public readonly string $type,
    #[SerializedName('media_type')] public readonly string $mediaType,
    public readonly string $data
  ) {}

  /**
   * Create a new Source instance from a base64 encoded data string.
   *
   * @param string $mediaType
   * @param string $data
   * @return Source
   */
  public static function fromBase64Data(string $mediaType, string $data): Source {
    return new Source('base64', $mediaType, $data);
  }

}
