<?php

namespace AiBundle\Examples;

use AiBundle\LLM\AbstractLLM;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractExampleCommand extends Command {

  private const DEFAULT_LLM = 'ollama';
  protected AbstractLLM $llm;

  public function __construct(
    #[Autowire('@service_container')] private readonly Container $container
  ) {
    parent::__construct();
  }
  protected function configure() {
    $this->addOption(
      'llm',
      'l',
      InputOption::VALUE_REQUIRED,
      'The LLM to use',
      self::DEFAULT_LLM
    );
  }
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $llm = $input->getOption('llm');
    $this->llm = $this->container->get('ai_bundle.llm.'.$llm);

    $output->writeln('Using LLM: '.get_class($this->llm));

    return Command::SUCCESS;
  }

}
