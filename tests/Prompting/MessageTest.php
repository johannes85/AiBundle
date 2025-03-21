<?php

namespace AiBundle\Tests\Prompting;

use AiBundle\Prompting\{Message, MessageRole};
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase {

  public function test_applyPlaceholders() {
    $message = new Message(
      MessageRole::HUMAN,
      'content {{key1}} {{key2}} {{invalidKey}}'
    );
    $res = $message->applyPlaceholders([
      'key1' => 'value1',
      'key2' => 'value2 {{keyInValue}}',
      'keyInValue' => 'value3'
    ]);
    $this->assertEquals(
      'content value1 value2 {{keyInValue}} ',
      $res->content
    );
    $this->assertNotSame($message, $res);
  }

  public function test_applyPlaceholders_scalerTypes() {
    $message = new Message(
      MessageRole::HUMAN,
      'content {{bool}} {{int}} {{float}} {{string}}'
    );
    $res = $message->applyPlaceholders([
      'bool' => true,
      'int' => 123,
      'float' => 123.45,
      'string' => 'Test'
    ]);
    $this->assertEquals(
      'content 1 123 123.45 Test',
      $res->content
    );
    $this->assertNotSame($message, $res);
  }

}
