<?php

namespace AiBundle\Examples;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\Json\Attributes\Description;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMException;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Toolbox;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Coordinates {
  public function __construct(
    #[Description('Latitude, for example 52.52')]
    public readonly string $latitude,
    #[Description('Longitude, for example 13.41')]
    public readonly string $longitude,
  ) {}
}

#[AsCommand('examples:tool-call')]
class ToolCallCommand extends AbstractExampleCommand {

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

    $output->writeln(
      $this->llm->generate(
        [
          new Message(
            MessageRole::HUMAN,
            'What is the current weather in Karlsruhe (49° 1′ N , 8° 24′ O) and Stuttgart (48° 47′ N, 9° 11′ O), Germany'
          )
        ],
        (new GenerateOptions())
          ->setTemperature(0.8),
        toolbox: new Toolbox(
          new Tool(
            'getWeather',
            'Retrieves current weather',
            function (Coordinates $coordinates) use ($output) {
              $output->writeln('Called tool with coordinates: '.json_encode($coordinates));
              return file_get_contents(
                'https://api.open-meteo.com/v1/forecast?latitude=' . $coordinates->latitude . '&longitude=' . $coordinates->longitude . '&current=temperature,windspeed'
              );
            }
          )
        )
      )->message->content
    );

    return Command::SUCCESS;
  }

}
