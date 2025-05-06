<?php

namespace AiBundle\Tests\Prompting;

use AiBundle\LLM\LLMException;
use AiBundle\Prompting\Tools\CallbackTool;
use AiBundle\Prompting\Tools\Toolbox;
use PHPUnit\Framework\TestCase;

class ToolboxTest extends TestCase {

  public function test_ensureMaxLLMCalls_tooMuch(): void {
    $this->expectException(LLMException::class);
    $this->expectExceptionMessage('Maximum number of 5 LLM calls exceeded.');
    (new Toolbox([], maxLLMCalls: 5))->ensureMaxLLMCalls(6);
  }

  public function test_ensureMaxLLMCalls_tooMuchDefaultValue(): void {
    $this->expectException(LLMException::class);
    $this->expectExceptionMessage('Maximum number of 10 LLM calls exceeded. This is set to a conservative value by default to avoid accidental overuse. You can set a higher value with setMaxLLMCalls(...) of your Toolbox instance.');
    (new Toolbox([]))->ensureMaxLLMCalls(11);
  }

  public function test_assertMaxLLMCalls(): void {
    $this->expectNotToPerformAssertions();
    (new Toolbox([], maxLLMCalls: 5))->ensureMaxLLMCalls(5);
    (new Toolbox([], maxLLMCalls: 5))->ensureMaxLLMCalls(4);
  }

  public function test_ignoreInvalidToolChoiceTrue(): void {
    $toolbox = new Toolbox([], ignoreInvalidToolChoice: true);
    $tool = $toolbox->getTool('non_existent_tool');
    $this->assertNotNull($tool);
    $this->assertEquals('not_found', $tool->name);
    $this->assertInstanceOf(CallbackTool::class, $tool);
    $callbackFn = $tool->callback;
    $this->assertEquals('Tool "non_existent_tool" not found', $callbackFn());
  }

  public function test_ignoreInvalidToolChoicFalsee(): void {
    $toolbox = new Toolbox([], ignoreInvalidToolChoice: false);
    $tool = $toolbox->getTool('non_existent_tool');
    $this->assertNull($tool);
  }

}
