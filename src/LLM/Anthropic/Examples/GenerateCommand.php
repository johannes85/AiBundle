<?php

namespace AiBundle\LLM\Anthropic\Examples;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMException;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Messages;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AiBundle\LLM\Anthropic\Anthropic;

class CountryInfo {
  public string $name;

  private string $capital;
  public function setCapital(string $capital): void {
    $this->capital = strtoupper($capital);
  }

  public function getCapital(): string {
    return $this->capital;
  }

  /** @var array<string> */
  #[ArrayType(itemType: 'string')]
  public array $languages;
}

#[AsCommand('anthropic:generate')]
class GenerateCommand extends Command {

  public function __construct(
    private Anthropic $anthropic
  ) {
    parent::__construct(null);
  }

  /**
   * @inheritDoc
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   * @throws LLMException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln(
      $this->anthropic->generate(
        (new Messages(
          new Message(MessageRole::SYSTEM, 'You are a helpful assistant for the user: {{user_name}}'),
          new Message(MessageRole::HUMAN, 'What is my name?')
        ))->processMessages(['user_name' => 'John']),
        (new GenerateOptions())
          ->setTemperature(0.8)
      )->message->content
    );

    $res =  $this->anthropic->generateData([
      new Message(MessageRole::HUMAN, 'Tell me about Canada')
    ], CountryInfo::class);

    print_r($res->data);

    return Command::SUCCESS;
  }

}
