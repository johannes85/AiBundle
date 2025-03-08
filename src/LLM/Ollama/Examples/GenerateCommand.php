<?php

namespace Johannes85\AiBundle\LLM\Ollama\Examples;

use Johannes85\AiBundle\LLM\Ollama\Ollama;
use Johannes85\AiBundle\Prompting\Message;
use Johannes85\AiBundle\Prompting\MessageRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln(
      $this->ollama->generate([
        new Message(MessageRole::HUMAN, 'Say Hello World')
      ])->getMessage()->getContent()
    );
    return Command::SUCCESS;
  }

}
