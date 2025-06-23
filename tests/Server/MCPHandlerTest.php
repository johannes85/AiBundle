<?php

namespace AiBundle\Tests\Server;

use AiBundle\MCP\Dto\ImageContent;
use AiBundle\MCP\Dto\TextContent;
use AiBundle\MCP\Server\MCPHandler;
use AiBundle\MCP\Server\ToolRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Serializer\Serializer;

class MCPHandlerTest extends TestCase {

  private MCPHandler $instance;
  private Serializer|MockObject $mockSerializer;
  private ToolRegistry|MockObject $mockToolRegistry;
  private Container|MockObject $mockContainer;

  protected function setUp(): void {
    $this->instance = new MCPHandler(
      $this->mockSerializer = $this->createMock(Serializer::class),
      $this->mockToolRegistry = $this->createMock(ToolRegistry::class),
      $this->mockContainer = $this->createMock(Container::class),
      [
        'name' => 'Test name',
        'title' => 'Test title',
        'version' => '1.2.3',
        'instructions' => 'Test instructions'
      ]
    );
  }

  private function callPrivateMethod(string $methodName, mixed ...$arguments): mixed {
    $reflectionMethod = new ReflectionMethod($this->instance, $methodName);
    $reflectionMethod->setAccessible(true);
    return $reflectionMethod->invokeArgs($this->instance, $arguments);
  }

  public static function toolResponses(): array {
    return [
      [null, 'null'],
      ['', ''],
      ['test response', 'test response'],
      [['key' => 'value'], '{"key":"value"}'],
      [new class { public string $key = 'value'; }, '{"key":"value"}']
    ];
  }

  #[DataProvider('toolResponses')]
  public function test_createContentForToolResponse(mixed $res, mixed $expected): void {
    $res = $this->callPrivateMethod('createContentArrayForToolResponse', $res);
    $this->assertEquals(
      [new TextContent($expected)],
      $res
    );
  }

  public function test_createContentForToolResponse_contentArray(): void {
    $res = $this->callPrivateMethod(
      'createContentArrayForToolResponse',
      [new TextContent('foo'), new ImageContent('foo', 'image/png')]
    );
    $this->assertEquals([new TextContent('foo'), new ImageContent('foo', 'image/png')], $res);
  }

  public function test_createContentForToolResponse_mixedArray(): void {
    $res = $this->callPrivateMethod(
      'createContentArrayForToolResponse',
      [new TextContent('foo'), 'bar']
    );
    $this->assertEquals([new TextContent('[{"type":"text","text":"foo"},"bar"]')], $res);
  }

}
