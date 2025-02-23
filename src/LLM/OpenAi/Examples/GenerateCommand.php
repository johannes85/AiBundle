<?php

namespace Johannes85\AiBundle\LLM\OpenAi\Examples;

use Johannes85\AiBundle\Prompting\Message;
use Johannes85\AiBundle\Prompting\MessageRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Johannes85\AiBundle\LLM\OpenAi\OpenAi;

#[AsCommand('openai:generate')]
class GenerateCommand extends Command {

  public function __construct(
    private OpenAi $openai
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
      $this->openai->generate([
        new Message(MessageRole::HUMAN, 'Say Hello World')
      ])->getMessage()->getContent()
    );
    return Command::SUCCESS;
  }

}
