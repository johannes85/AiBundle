<?php

namespace AiBundle\LLM\Ollama\Examples;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMException;
use AiBundle\LLM\Ollama\Ollama;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CountryInfo {
  public string $name;
  public string $capital;

  /** @var array<string> */
  #[ArrayType(itemType: 'string')]
  public array $languages;
}

#[AsCommand('ollama:generate')]
class GenerateCommand extends Command {

  public function __construct(
    private Ollama $ollama
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
      $this->ollama->generate(
        [
          new Message(MessageRole::HUMAN, 'Say Hello World')
        ],
        (new GenerateOptions())
          ->setTemperature(0.8)
      )->getMessage()->getContent()
    );

    $res = $this->ollama->generateData([
      new Message(MessageRole::HUMAN, 'Tell me about Canada')
    ], CountryInfo::class);

    $output->writeln(json_encode($res->getData(), JSON_PRETTY_PRINT));

    return Command::SUCCESS;
  }

}
