<?php

namespace AiBundle\Examples;

use AiBundle\LLM\LLMException;
use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\MessageStore\MessageStoreDebugTap;
use AiBundle\Prompting\MessageStore\Psr6CacheMessageStore;
use AiBundle\Prompting\PersistentMessages;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\MissingInputException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Uid\Uuid;

#[AsCommand('examples:persistent-chat')]
class PersistentChatCommand extends AbstractExampleCommand {

  private const CHAT_UUID_NAMESPACE = '521b8d45-c08d-4272-a2d0-ec21eb565a7b';

  public function __construct(
    #[Autowire('@service_container')] Container $container,
    private readonly Psr6CacheMessageStore $messageStore
  ) {
    parent::__construct($container);
  }

  /**
   * @inheritDoc
   */
  protected function configure() {
    parent::configure();

    $this->addArgument('uid', InputArgument::OPTIONAL, 'Session UID', null);
  }

  /**
   * @inheritDoc
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @return int
   * @throws MissingInputException
   * @throws RuntimeException
   * @throws LLMException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    parent::execute($input, $output);

    /** @var QuestionHelper $helper */
    $helper = $this->getHelper('question');
    $question = new Question('👤 ');

    $uuid = $input->getArgument('uid') ? Uuid::v3(
      Uuid::fromString(self::CHAT_UUID_NAMESPACE),
      $input->getArgument('uid')
    ) : Uuid::v4();

    $messages = new PersistentMessages(
      new MessageStoreDebugTap($this->messageStore, $output),
      [new Message(
        MessageRole::SYSTEM,
        'You are a helpful assistant for the user with the name: {{user_name}}',
        true
      )],
      $uuid
    );
    $output->writeln(sprintf('Started chat with session ID: %s', $messages->sessionUid));

    while ($q = $helper->ask($input, $output, $question)) {
      $messages->addMessage(new Message(MessageRole::HUMAN, $q));
      $res = $this->llm->generate($messages->processMessages([
        'user_name' => 'Bob'
      ]));
      $messages->applyResponseAndPersist($res);

      $output->writeln(sprintf('🤖 %s', trim($res->message->content)));
    }

    return Command::SUCCESS;
  }

}
