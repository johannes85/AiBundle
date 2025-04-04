<?php

namespace AiBundle\LLM\OpenAi\Dto;

use AiBundle\LLM\GenerateOptions;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

class ChatCompletionRequest {

  #[SerializedName('frequency_penalty')] private ?float $frequencyPenalty = null;
  #[SerializedName('max_completion_tokens')] private ?int $maxCompletionTokens = null;
  #[SerializedName('presence_penalty')] private ?int $presencePenalty = null;
  #[SerializedName('reasoning_effort')] private ?string $reasoningEffort = null;
  private ?int $seed = null;
  #[SerializedName('service_tier')] private ?string $serviceTier = null;

  /** @var string|array<string>|null */
  private string|array|null $stop = null;
  private ?bool $store = null;
  private ?bool $stream = null;
  private ?float $temperature = null;

  /** @var array<OpenAiTool>|null */
  private ?array $tools = null;

  #[SerializedName('top_logprobs')] private ?string $topLogprobs = null;
  #[SerializedName('top_p')] private ?float $topP = null;
  private ?string $user = null;

  /** @var ?ResponseFormat */
  #[SerializedName('response_format')] private ?ResponseFormat $responseFormat = null;

  /**
   * @param string $model
   * @param array<OpenAiMessage> $messages
   */
  public function __construct(
    private string $model,
    private array $messages
  ) {}

  /**
   * Create instance from LLM GenerateOptions
   *
   * @param string $model
   * @param array<OpenAiMessage> $messages
   * @param ?GenerateOptions $options
   * @return self
   */
  public static function fromGenerateOptions(
    string $model,
    array $messages,
    ?GenerateOptions $options = null
  ): self {
    $ret = new self($model, $messages);
    if ($options !== null) {
      $ret
        ->setTemperature($options->getTemperature())
        ->setMaxCompletionTokens($options->getMaxOutputTokens());
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

  public function setModel(string $model): ChatCompletionRequest {
    $this->model = $model;
    return $this;
  }

  /**
   * @return array<OpenAiMessage>
   */
  public function getMessages(): array {
    return $this->messages;
  }

  /**
   * @param array<OpenAiMessage> $messages
   * @return $this
   */
  public function setMessages(array $messages): ChatCompletionRequest {
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

  public function getMaxCompletionTokens(): ?int {
    return $this->maxCompletionTokens;
  }

  public function setMaxCompletionTokens(?int $maxCompletionTokens): static {
    $this->maxCompletionTokens = $maxCompletionTokens;
    return $this;
  }

  public function getPresencePenalty(): ?int {
    return $this->presencePenalty;
  }

  public function setPresencePenalty(?int $presencePenalty): static {
    $this->presencePenalty = $presencePenalty;
    return $this;
  }

  public function getReasoningEffort(): ?string {
    return $this->reasoningEffort;
  }

  public function setReasoningEffort(?string $reasoningEffort): static {
    $this->reasoningEffort = $reasoningEffort;
    return $this;
  }

  public function getSeed(): ?int {
    return $this->seed;
  }

  public function setSeed(?int $seed): static {
    $this->seed = $seed;
    return $this;
  }

  public function getServiceTier(): ?string {
    return $this->serviceTier;
  }

  public function setServiceTier(?string $serviceTier): static {
    $this->serviceTier = $serviceTier;
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

  public function getStore(): ?bool {
    return $this->store;
  }

  public function setStore(?bool $store): static {
    $this->store = $store;
    return $this;
  }

  public function getStream(): ?bool {
    return $this->stream;
  }

  public function setStream(?bool $stream): static {
    $this->stream = $stream;
    return $this;
  }

  public function getTemperature(): ?float {
    return $this->temperature;
  }

  public function setTemperature(?float $temperature): static {
    $this->temperature = $temperature;
    return $this;
  }

  /**
   * @return array<OpenAiTool>|null
   */
  public function getTools(): ?array {
    return $this->tools;
  }

  /**
   * @param array<OpenAiTool>|null $tools
   * @return $this
   */
  public function setTools(?array $tools): ChatCompletionRequest {
    $this->tools = $tools;
    return $this;
  }

  public function getTopLogprobs(): ?string {
    return $this->topLogprobs;
  }

  public function setTopLogprobs(?string $topLogprobs): static {
    $this->topLogprobs = $topLogprobs;
    return $this;
  }

  public function getTopP(): ?float {
    return $this->topP;
  }

  public function setTopP(?float $topP): static {
    $this->topP = $topP;
    return $this;
  }

  public function getUser(): ?string {
    return $this->user;
  }

  public function setUser(?string $user): static {
    $this->user = $user;
    return $this;
  }

  public function getResponseFormat(): ?ResponseFormat {
    return $this->responseFormat;
  }

  public function setResponseFormat(?ResponseFormat $responseFormat): ChatCompletionRequest {
    $this->responseFormat = $responseFormat;
    return $this;
  }

}
