<?php

namespace AiBundle\Examples;

use AiBundle\LLM\LLMException;
use AiBundle\Prompting\File;
use AiBundle\Prompting\FileType;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('examples:detect-cars')]
class DetectCars extends AbstractExampleCommand {

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

    $ret = $this->llm->generate([
      new Message(
        MessageRole::HUMAN,
        <<<PROMPT
        How many cars are in the driveway and what are the brands and color of those cars.
        PROMPT,
        files: [File::fromPath(FileType::IMAGE, 'image/png', __DIR__.'/Resources/cars2.png')]
      )
    ]);

    $output->writeln($ret->message->content);

    return Command::SUCCESS;
  }

}
