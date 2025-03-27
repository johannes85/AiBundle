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


class Receipt {

  public function __construct(
    public readonly float $totalAmmount,
    public readonly string $currency
  ) {}

}
#[AsCommand('examples:analyze-receipt')]
class AnalyzeReceiptCommand extends Command {

  public function __construct(
    private readonly Ollama $llm
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

    $ret = $this->llm->generateData([
      new Message(
        MessageRole::SYSTEM,
        <<<PROMPT
        Your task is to analyze a receipt image and answer questions about it.
        If you don't know the answer, don't worry, just leave it blank. Don't make up any information.
        PROMPT
      ),
      new Message(
        MessageRole::HUMAN,
        <<<PROMPT
        Please extract the required information from the receipt image.
        PROMPT,
        files: [File::fromPath(FileType::IMAGE, 'image/jpg', __DIR__.'/Resources/receipt.jpg')]
      )
    ], Receipt::class);

    $receipt = $ret->data;
    $output->writeln('Total ammount: '.$receipt->totalAmmount.' '.$receipt->currency);

    return Command::SUCCESS;
  }

}
