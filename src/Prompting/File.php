<?php

namespace AiBundle\Prompting;

class File {

  /**
   * @param FileType $type
   * @param string $mimeType
   * @param resource $stream
   */
  public function __construct(
    public readonly FileType $type,
    public readonly string $mimeType,
    private readonly mixed $stream,
  ) {}

  /**
   * Creates a new instance from a string.
   *
   * @param FileType $type
   * @param string $fileContent
   * @return self
   */
  public static function fromString(FileType $type, string $mimeType, string $fileContent): self {
    $stream = fopen('memory://', 'r+');
    fwrite($stream, $fileContent);
    rewind($stream);
    return new self($type, $mimeType, $stream);
  }

  /**
   * Cretaes a new instance from a file path.
   *
   * @param FileType $type
   * @param string $path
   * @return self
   */
  public static function fromPath(FileType $type, string $mimeType, string $path): self {
    $stream = fopen($path, 'r');
    return new self($type, $mimeType, $stream);
  }

  /**
   * Writes the file to a path.
   *
   * @param string $path
   * @return void
   */
  public function writeTo(string $path): void {
    $stream = fopen($path, 'w');
    stream_copy_to_stream($this->stream, $stream);
    fclose($stream);
  }

  /**
   * Returns content of the file as a base64 encoded string.
   *
   * @return string
   */
  public function getBase64Content(): string {
    return base64_encode(stream_get_contents($this->stream));
  }

}
