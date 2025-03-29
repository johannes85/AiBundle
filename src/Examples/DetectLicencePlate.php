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

class Car {
  public function __construct(
    public readonly string $licensePlate,
    public readonly string $manufacturer
  ) {}
}

#[AsCommand('examples:detect-licence-plate')]
class DetectLicencePlate extends AbstractExampleCommand {

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
        Please extract the content of the license plate and the car manufacturer from the image.
        PROMPT,
        files: [File::fromPath(FileType::IMAGE, 'image/png', __DIR__.'/Resources/car.png')]
      )
    ], responseDataType: Car::class);

    $output->writeln(json_encode($ret->data));

    return Command::SUCCESS;
  }

}
