<?php

namespace AiBundle\Examples;

use AiBundle\Json\Attributes\Description;
use AiBundle\LLM\GenerateOptions;
use AiBundle\LLM\LLMException;
use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\MCPClient;
use AiBundle\MCP\StdIoTransport;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Tools\Tool;
use AiBundle\Prompting\Tools\Toolbox;
use AiBundle\Prompting\Tools\ToolChoice;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Serializer\Serializer;

#[AsCommand('examples:mcp')]
class McpCommand extends Command {

  public function __construct(
    #[Autowire('@ai_bundle.rest.serializer')] private Serializer $serializer,
    private LoggerInterface $log
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

    $transport = new StdIoTransport(
      [
        'docker',
        'run',
        '--rm',
        '-i',
        'mcp/everything'
      ],
      $this->serializer,
      $this->log
    );
    $mcp = new MCPClient(
      $transport,
      $this->serializer
    );

    //dd($mcp->getTools());
    #dd($mcp->callTool('printEnv'));
    $output->writeln('1');
    print_r($mcp->callTool('add', ['a' => 1, 'b' => 2]));

    $output->writeln('2');
    print_r($mcp->callTool('add', ['a' => 1, 'b' => 2]));

    return Command::SUCCESS;
  }

}
