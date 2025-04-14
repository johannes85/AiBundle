<?php

namespace AiBundle\LLM\DeepSeek\Dto;

use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMCapabilityException;
use AiBundle\LLM\LLMException;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

class ChatCompletionRequest {

  #[SerializedName('frequency_penalty')] private ?float $frequencyPenalty = null;

  #[SerializedName('max_tokens')] private ?int $maxTokens = null;

  #[SerializedName('presence_penalty')] private ?int $presencePenalty = null;

  #[SerializedName('response_format')] private ?string $responseFormat = null;

  /** @var string|array<string>|null  */
  private null|string|array $stop = null;

  private ?bool $stream = null;

  /** @var array<mixed>|null  */
  #[SerializedName('stream_options')] private ?array $streamOptions = null;

  private ?float $temperature = null;

  #[SerializedName('top_p')] private ?float $topP = null;

  /** @var array<DeepSeekTool>|null */
  private ?array $tools = null;

  #[SerializedName('tool_choice')] private null|DeepSeekToolChoice|ToolChoiceType $toolChoice = null;

  private ?bool $logprobs = null;

  #[SerializedName('top_logprobs')] private ?int $topLogprobs = null;

  /**
   * @param string $model
   * @param array<DeepSeekMessage> $messages
   */
  public function __construct(
    private string $model,
    private array $messages
  ) {}

  /**
   * Create instance from LLM GenerateOptions
   *
   * @param string $model
   * @param array<DeepSeekMessage> $messages
   * @param ?GenerateOptions $options
   * @return self
   * @throws LLMException
   */
  public static function fromGenerateOptions(
    string $model,
    array $messages,
    ?GenerateOptions $options = null
  ): self {
    $ret = new self($model, $messages);
    if ($options !== null) {
      if ($options->getTopK() !== null) {
        throw new LLMCapabilityException("TopK isn't supported by this LLM");
      }
      $ret
        ->setTemperature($options->getTemperature())
        ->setMaxTokens($options->getMaxOutputTokens())
        ->setTopP($options->getTopP());
      foreach ($options->getCustomOptions() as $key => $value) {
        $method = 'set'.ucfirst(preg_replace_callback('/_(\w)/', fn($m) => strtoupper($m[1]), $key));
        if (!method_exists($ret, $method)) {
          throw new InvalidArgumentException(sprintf('Unknown custom option "%s"', $key));
        }
        $ret->$method($value);
      }
    }
    return $ret;
  }

  public function getModel(): string {
    return $this->model;
  }

  public function setModel(string $model): static {
    $this->model = $model;
    return $this;
  }

  /**
   * @return array<DeepSeekMessage>
   */
  public function getMessages(): array {
    return $this->messages;
  }

  /**
   * @param array<DeepSeekMessage> $messages
   * @return $this
   */
  public function setMessages(array $messages): static {
    $this->messages = $messages;
    return $this;
  }

  public function getFrequencyPenalty(): ?float {
    return $this->frequencyPenalty;
  }

  public function setFrequencyPenalty(?float $frequencyPenalty): static {
    $this->frequencyPenalty = $frequencyPenalty;
    return $this;
  }

  public function getMaxTokens(): ?int {
    return $this->maxTokens;
  }

  public function setMaxTokens(?int $maxTokens): static {
    $this->maxTokens = $maxTokens;
    return $this;
  }

  public function getPresencePenalty(): ?int {
    return $this->presencePenalty;
  }

  public function setPresencePenalty(?int $presencePenalty): static {
    $this->presencePenalty = $presencePenalty;
    return $this;
  }

  public function getResponseFormat(): ?string {
    return $this->responseFormat;
  }

  public function setResponseFormat(?string $responseFormat): static {
    $this->responseFormat = $responseFormat;
    return $this;
  }

  /**
   * @return array<string>|string|null
   */
  public function getStop(): array|string|null {
    return $this->stop;
  }

  /**
   * @param array<string>|string|null $stop
   * @return $this
   */
  public function setStop(array|string|null $stop): static {
    $this->stop = $stop;
    return $this;
  }

  public function getStream(): ?bool {
    return $this->stream;
  }

  public function setStream(?bool $stream): static {
    $this->stream = $stream;
    return $this;
  }

  /**
   * @return array<mixed>|null
   */
  public function getStreamOptions(): ?array {
    return $this->streamOptions;
  }

  /**
   * @param array<mixed>|null $streamOptions
   * @return $this
   */
  public function setStreamOptions(?array $streamOptions): static {
    $this->streamOptions = $streamOptions;
    return $this;
  }

  public function getTemperature(): ?float {
    return $this->temperature;
  }

  public function setTemperature(?float $temperature): static {
    $this->temperature = $temperature;
    return $this;
  }

  public function getTopP(): ?float {
    return $this->topP;
  }

  public function setTopP(?float $topP): static {
    $this->topP = $topP;
    return $this;
  }

  /**
   * @return array<DeepSeekTool>|null
   */
  public function getTools(): ?array {
    return $this->tools;
  }

  /**
   * @param array<DeepSeekTool>|null $tools
   * @return $this
   */
  public function setTools(?array $tools): static {
    $this->tools = $tools;
    return $this;
  }

  public function getToolChoice(): null|DeepSeekToolChoice|ToolChoiceType {
    return $this->toolChoice;
  }

  public function setToolChoice(null|DeepSeekToolChoice|ToolChoiceType $toolChoice): static {
    $this->toolChoice = $toolChoice;
    return $this;
  }

  public function getLogprobs(): ?bool {
    return $this->logprobs;
  }

  public function setLogprobs(?bool $logprobs): static {
    $this->logprobs = $logprobs;
    return $this;
  }

  public function getTopLogprobs(): ?int {
    return $this->topLogprobs;
  }

  public function setTopLogprobs(?int $topLogprobs): static {
    $this->topLogprobs = $topLogprobs;
    return $this;
  }

}
