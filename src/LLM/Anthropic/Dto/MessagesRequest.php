<?php

namespace AiBundle\LLM\Anthropic\Dto;

use AiBundle\LLM\GenerateOptions;
use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;

class MessagesRequest {

  private ?float $temperature = null;
  private ?string $system = null;
  #[SerializedName('tool_choice')] private ?AnthropicToolChoice $toolChoice = null;
  #[SerializedName('top_k')] private ?int $topK = null;
  #[SerializedName('top_p')] private ?float $topP = null;


  /**
   * @var array<AnthropicTool>|null
   */
  private ?array $tools = null;

  /**
   * @param string $model
   * @param array<AnthropicMessage> $messages
   * @param int $maxTokens
   */
  public function __construct(
    public readonly string $model,
    public readonly array $messages,
    #[SerializedName('max_tokens')] public readonly int $maxTokens
  ) {}

  /**
   * Create instance from LLM GenerateOptions
   *
   * @param string $model
   * @param array<AnthropicMessage> $messages
   * @param int $maxTokens
   * @param ?GenerateOptions $options
   * @return self
   */
  public static function fromGenerateOptions(
    string $model,
    array $messages,
    int $maxTokens,
    ?GenerateOptions $options = null
  ): self {
    if ($options && ($oTokens = $options->getMaxOutputTokens()) !== null) {
      $maxTokens = $oTokens;
    }
    $ret = new self($model, $messages, $maxTokens);
    if ($options !== null) {
      $ret
        ->setTemperature($options->getTemperature())
        ->setTopK($options->getTopK())
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

  /**
   * @return float|null
   */
  public function getTemperature(): ?float {
    return $this->temperature;
  }

  /**
   * @param float|null $temperature
   * @return static
   */
  public function setTemperature(?float $temperature): static {
    $this->temperature = $temperature;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getSystem(): ?string {
    return $this->system;
  }

  /**
   * @param string|null $system
   * @return static
   */
  public function setSystem(?string $system): static {
    $this->system = $system;
    return $this;
  }

  /**
   * @return AnthropicToolChoice|null
   */
  public function getToolChoice(): ?AnthropicToolChoice {
    return $this->toolChoice;
  }

  /**
   * @param AnthropicToolChoice|null $toolChoice
   * @return static
   */
  public function setToolChoice(?AnthropicToolChoice $toolChoice): static {
    $this->toolChoice = $toolChoice;
    return $this;
  }

  /**
   * @return array<AnthropicTool>|null
   */
  public function getTools(): ?array {
    return $this->tools;
  }

  /**
   * @param array<AnthropicTool>|null $tools
   * @return static
   */
  public function setTools(?array $tools): static {
    $this->tools = $tools;
    return $this;
  }

  public function getTopK(): ?int {
    return $this->topK;
  }

  public function setTopK(?int $topK): static {
    $this->topK = $topK;
    return $this;
  }

  public function getTopP(): ?float {
    return $this->topP;
  }

  public function setTopP(?float $topP): static {
    $this->topP = $topP;
    return $this;
  }

}
