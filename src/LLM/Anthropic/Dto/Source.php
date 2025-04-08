<?php

namespace AiBundle\LLM\Anthropic\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class Source {

  public function __construct(
    public string $type,
    #[SerializedName('media_type')] public string $mediaType,
    public string $data
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
