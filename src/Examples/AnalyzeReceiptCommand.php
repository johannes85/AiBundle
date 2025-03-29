<?php

namespace AiBundle\Examples;

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

class Receipt {

  public function __construct(
    public readonly float $totalAmmount,
    public readonly string $currency
  ) {}

}
#[AsCommand('examples:analyze-receipt')]
class AnalyzeReceiptCommand extends AbstractExampleCommand {

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
        files: [File::fromPath(FileType::IMAGE, 'image/jpeg', __DIR__.'/Resources/receipt.jpg')]
      )
    ], responseDataType: Receipt::class);

    $receipt = $ret->data;
    $output->writeln('Total ammount: '.$receipt->totalAmmount.' '.$receipt->currency);

    return Command::SUCCESS;
  }

}
