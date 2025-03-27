<?php

namespace AiBundle\Examples;

use AiBundle\Json\Attributes\ArrayType;
use AiBundle\LLM\GoogleAi\GoogleAi;
use AiBundle\LLM\LLMException;
use AiBundle\LLM\Ollama\Ollama;
use AiBundle\Prompting\File;
use AiBundle\Prompting\FileType;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand('examples:solve-captcha')]
class SolveCaptchaCommand extends Command {
  public function __construct(
    private readonly GoogleAi $llm
  ) {
    parent::__construct();
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

    $ret = $this->llm->generate([
      new Message(
        MessageRole::HUMAN,
        <<<PROMPT
        Please extract the math problem from the image and solve it.
        Return the result as a number only.
        PROMPT,
        files: [File::fromPath(FileType::IMAGE, 'image/png', __DIR__.'/Resources/captcha.png')]
      )
    ]);

    $output->writeln($ret->message);

    return Command::SUCCESS;
  }

}
