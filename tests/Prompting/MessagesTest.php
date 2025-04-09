<?php

namespace AiBundle\Tests\Prompting;

use AiBundle\Prompting\Message;
use AiBundle\Prompting\MessageRole;
use AiBundle\Prompting\Messages;
use PHPUnit\Framework\TestCase;

class MessagesTest extends TestCase {

  public function test_processMessages(): void {
    $messages = (new Messages(
      new Message(
        MessageRole::HUMAN,
        'content {{placeholder1}}',
        true
      ),
      new Message(
        MessageRole::AI,
        'content {{placeholder1}}',
        false
      )
    ));
    $this->assertEquals(
      [
        new Message(
          MessageRole::HUMAN,
          'content replaceholder1',
          false
        ),
        new Message(
          MessageRole::AI,
          'content {{placeholder1}}',
          false
        )
      ],
      $messages->processMessages(['placeholder1' => 'replaceholder1'])
    );

  }

}
