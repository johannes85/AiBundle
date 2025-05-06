<?php

namespace AiBundle\Examples;

use AiBundle\LLM\LLMException;
use AiBundle\MCP\MCPServer;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Toolbox;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Container;

#[AsCommand('examples:mcp')]
class McpCommand extends AbstractExampleCommand {

  public function __construct(
    #[Autowire('@ai_bundle.mcp.example_everything')] private MCPServer $mcp,
    #[Autowire('@service_container')] Container $container
  ) {
    parent::__construct($container);
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
    parent::execute($input, $output);

    $mcpToolbox = new Toolbox(
      [...$this->mcp->getTools()]
    );

    $res = $this->llm->generate(
      [
        new Message(MessageRole::HUMAN, 'Add 1 and 2')
      ],
      toolbox: $mcpToolbox
    );

    $output->writeln($res->message->content);
    $output->writeln(sprintf(
      'Completed with %d LLM calls.',
      $res->usage->llmCalls
    ));

    return Command::SUCCESS;
  }

}
