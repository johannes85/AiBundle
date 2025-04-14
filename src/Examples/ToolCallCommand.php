<?php

namespace AiBundle\Examples;

use AiBundle\Json\Attributes\Description;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMException;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolChoice;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    $res = $this->llm->generate(
      [
        new Message(
          MessageRole::HUMAN,
          'What is the current weather in Karlsruhe (49° 1′ N , 8° 24′ O) and Stuttgart (48° 47′ N, 9° 11′ O), Germany'
        )
      ],
      (new GenerateOptions())
        ->setTemperature(0.8),
      toolbox: (new Toolbox(
        [
          new Tool(
            'getWeather',
            'Retrieves current weather',
            function (
              #[Description('Latitude, for example 52.52')] string $latitude,
              #[Description('Longitude, for example 13.41')] string $longitude
            ) use ($output) {
              $output->writeln('Called tool with coordinates: '.$latitude . ', ' . $longitude);
              return file_get_contents(
                'https://api.open-meteo.com/v1/forecast?latitude=' . $latitude . '&longitude=' . $longitude . '&current=temperature,windspeed'
              );
            }
          )
        ],
        toolChoice: ToolChoice::AUTO,
        maxLLMCalls: 5
      ))
    );

    $output->writeln($res->message->content);
    $output->writeln(sprintf(
      'Completed with %d LLM calls.',
      $res->usage->llmCalls
    ));

    return Command::SUCCESS;
  }

}
