<?php

namespace AiBundle\LLM\OpenAi\Examples;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMException;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AiBundle\LLM\OpenAi\OpenAi;

class CountryInfo {
  public string $name;
  public string $capital;

  /** @var array<string> */
  #[ArrayType(itemType: 'string')]
  public array $languages;
}

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
   * @throws LLMException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln(
      $this->openai->generate(
        [
          new Message(MessageRole::HUMAN, 'Say Hello World')
        ]
      )->getMessage()->getContent()
    );

    $res =  $this->openai->generateData([
      new Message(MessageRole::HUMAN, 'Tell me about Canada')
    ], CountryInfo::class);

    $output->writeln(json_encode($res->getData(), JSON_PRETTY_PRINT));

    return Command::SUCCESS;
  }

}
