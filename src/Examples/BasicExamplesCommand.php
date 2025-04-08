<?php

namespace AiBundle\Examples;

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

#[AsCommand('examples:basic')]
class BasicExamplesCommand extends AbstractExampleCommand {

  /**
   * @inheritDoc
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   * @throws LLMException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    parent::execute($input, $output);

    $res = $this->llm->generate(
      (new Messages(
        new Message(MessageRole::SYSTEM, 'You are a helpful assistant for the user: {{user_name}}', true),
        new Message(MessageRole::HUMAN, 'What is my name?')
      ))->processMessages(['user_name' => 'John']),
      (new GenerateOptions())
        ->setTemperature(0.8)
    );
    $output->writeln($res->message->content);
    $output->writeln(sprintf(
      'Tokens usage: [input: %d] [output: %d]',
      $res->usage->inputTokens,
      $res->usage->outputTokens
    ));

    $res =  $this->llm->generate([
      new Message(MessageRole::HUMAN, 'Tell me about Canada')
    ], responseDataType: CountryInfo::class);

    print_r($res->data);

    return Command::SUCCESS;
  }

}
