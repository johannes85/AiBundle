<?php

namespace Johannes85\AiBundle\LLM\Ollama\Examples;

use Johannes85\AiBundle\LLM\Ollama\Ollama;
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

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln(
      $this->ollama->generate('Say Hello World')
    );
    return Command::SUCCESS;
  }

}
