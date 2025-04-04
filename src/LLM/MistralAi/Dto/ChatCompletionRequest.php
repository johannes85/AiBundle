<?php

namespace AiBundle\LLM\MistralAi\Dto;

use AiBundle\LLM\GenerateOptions;
use Symfony\Component\Serializer\Attribute\SerializedName;
use InvalidArgumentException;

class ChatCompletionRequest {

  private ?float $temperature;
  #[SerializedName('top_p')] private ?float $topP = null;
  #[SerializedName('max_tokens')] private ?float $maxTokens = null;
  private ?bool $stream = null;

  /** @var string|array<mixed>|null */
  private string|array|null $stop = null;
  #[SerializedName('random_seed')] private ?int $randomSeed = null;
  #[SerializedName('response_format')] private ?ResponseFormat $responseFormat = null;

  /** @var array<MistralTool>|null  */
  private ?array $tools = null;
  #[SerializedName('presence_penalty')] private ?float $presencePenalty = null;

  /** @var array<mixed>|string|null */
  #[SerializedName('tool_choice')] private null|array|string $toolChoice = null;
  #[SerializedName('frequency_penalty')] private ?float $frequencyPenalty = null;
  private ?int $n = null;

  /** @var array<mixed>|null  */
  private ?array $prediction = null;
  #[SerializedName('parallel_tool_calls')] private ?bool $parallelToolCalls = null;
  #[SerializedName('safe_prompt')] private ?bool $safePrompt = null;

  /**
   * @param string $model
   * @param array<MistralAiMessage> $messages
   */
  public function __construct(
    private string $model,
    private array $messages
  ) {}

  /**
   * Create instance from LLM GenerateOptions
   *
   * @param string $model
   * @param array<MistralAiMessage> $messages
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
        ->setMaxTokens($options->getMaxOutputTokens());
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
   * @return array<MistralAiMessage>
   */
  public function getMessages(): array {
    return $this->messages;
  }

  /**
   * @param array<MistralAiMessage> $messages
   * @return $this
   */
  public function setMessages(array $messages): static {
    $this->messages = $messages;
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

  public function getMaxTokens(): ?float {
    return $this->maxTokens;
  }

  public function setMaxTokens(?float $maxTokens): static {
    $this->maxTokens = $maxTokens;
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
   * @return array<mixed>|string|null
   */
  public function getStop(): array|string|null {
    return $this->stop;
  }

  /**
   * @param array<mixed>|string|null $stop
   * @return $this
   */
  public function setStop(array|string|null $stop): static {
    $this->stop = $stop;
    return $this;
  }

  public function getRandomSeed(): ?int {
    return $this->randomSeed;
  }

  public function setRandomSeed(?int $randomSeed): static {
    $this->randomSeed = $randomSeed;
    return $this;
  }

  public function getResponseFormat(): ?ResponseFormat {
    return $this->responseFormat;
  }

  public function setResponseFormat(?ResponseFormat $responseFormat): static {
    $this->responseFormat = $responseFormat;
    return $this;
  }

  /**
   * @return array<mixed>|null
   */
  public function getTools(): ?array {
    return $this->tools;
  }

  /**
   * @param array<mixed>|null $tools
   * @return $this
   */
  public function setTools(?array $tools): static {
    $this->tools = $tools;
    return $this;
  }

  public function getPresencePenalty(): ?float {
    return $this->presencePenalty;
  }

  public function setPresencePenalty(?float $presencePenalty): static {
    $this->presencePenalty = $presencePenalty;
    return $this;
  }

  /**
   * @return array<mixed>|string|null
   */
  public function getToolChoice(): array|string|null {
    return $this->toolChoice;
  }

  /**
   * @param array<mixed>|string|null $toolChoice
   * @return $this
   */
  public function setToolChoice(array|string|null $toolChoice): static {
    $this->toolChoice = $toolChoice;
    return $this;
  }

  public function getFrequencyPenalty(): ?float {
    return $this->frequencyPenalty;
  }

  public function setFrequencyPenalty(?float $frequencyPenalty): static {
    $this->frequencyPenalty = $frequencyPenalty;
    return $this;
  }

  public function getN(): ?int {
    return $this->n;
  }

  public function setN(?int $n): static {
    $this->n = $n;
    return $this;
  }

  /**
   * @return array<mixed>|null
   */
  public function getPrediction(): ?array {
    return $this->prediction;
  }

  /**
   * @param array<mixed>|null $prediction
   * @return $this
   */
  public function setPrediction(?array $prediction): static {
    $this->prediction = $prediction;
    return $this;
  }

  public function getParallelToolCalls(): ?bool {
    return $this->parallelToolCalls;
  }

  public function setParallelToolCalls(?bool $parallelToolCalls): static {
    $this->parallelToolCalls = $parallelToolCalls;
    return $this;
  }

  public function getSafePrompt(): ?bool {
    return $this->safePrompt;
  }

  public function setSafePrompt(?bool $safePrompt): static {
    $this->safePrompt = $safePrompt;
    return $this;
  }

}
