<?php

namespace AiBundle\Prompting\Tools;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use AiBundle\MCP\MCPException;
use AiBundle\MCP\MCPTool;
use InvalidArgumentException;
use phpDocumentor\Reflection\Types\True_;
use Psr\Log\LoggerInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use Stringable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

class ToolsHelper {

  public function __construct(
    private readonly SchemaGenerator $schemaGenerator,
    #[Autowire('@ai_bundle.serializer')] private readonly Serializer $serializer,
    private readonly LoggerInterface $log
  ) {}

  /**
   * Returns schema of tool callback parameters as object
   *
   * @param AbstractTool $tool
   * @return array<mixed>
   * @throws ToolsHelperException
   */
  public function getToolCallbackSchema(AbstractTool $tool): array {
    try {
      return match (true) {
        $tool instanceof CallbackTool => $this->schemaGenerator->generateForClosureParameters($tool->callback),
        $tool instanceof MCPTool => $tool->schema,
        default => throw new InvalidArgumentException('Unsupported tool type:'.get_class($tool)),
      };
    } catch (ReflectionException $ex) {
      throw new ToolsHelperException('Error getting tool callback parameters via reflection', previous: $ex);
    } catch (SchemaGeneratorException $ex) {
      throw new ToolsHelperException('Error generating schema for tool callback', previous: $ex);
    }
  }

  /**
   * Calls tool with arguments string returned by LLM
   *
   * @param AbstractTool $tool
   * @param string|array<mixed> $arguments
   * @return mixed
   * @throws ReflectionException
   * @throws ToolsHelperException
   * @throws MCPException
   */
  public function callTool(AbstractTool $tool, string|array $arguments): mixed {
    if (is_string($arguments)) {
      $arguments = json_decode($arguments, true);
    }
    $this->log->debug(sprintf(
      'Calling tool "%s" (%s) with arguments: %s',
      $tool->name,
      get_class($tool),
      json_encode($arguments)
    ));
    $ret = match (true) {
      $tool instanceof CallbackTool => $this->callCallbackTool($tool, $arguments),
      $tool instanceof MCPTool => $this->callMCPTool($tool, $arguments),
      default => throw new InvalidArgumentException('Unsupported tool type:'.get_class($tool)),
    };
    $this->log->debug(sprintf(
      'Got answer: %s',
      $ret
    ));
    return $ret;
  }

  /**
   * Calls MCP Tool
   *
   * @param MCPTool $tool
   * @param array<mixed> $arguments
   * @return mixed
   * @throws MCPException
   */
  private function callMCPTool(MCPTool $tool, array $arguments): mixed {
    return $tool->call($arguments);
  }

  /**
   * Calls callback tool with arguments string returned by LLM, matching the tool callback schema
   *
   * @param CallbackTool $tool
   * @param array<mixed> $arguments
   * @return mixed
   * @throws ReflectionException
   * @throws ToolsHelperException
   */
  private function callCallbackTool(CallbackTool $tool, array $arguments): mixed {
    $params = (new ReflectionFunction($tool->callback))->getParameters();
    $paramCallValues = [];
    foreach ($params as $param) {
      $paramType = $param->getType();
      if ($paramType instanceof ReflectionNamedType) {
        if (isset($arguments[$param->getName()])) {
          if ($paramType->isBuiltin()) {
            $paramCallValues[$param->getName()] = $arguments[$param->getName()];
          } else {
            try {
              $paramCallValues[$param->getName()] = $this->serializer->denormalize(
                $arguments[$param->getName()],
                $paramType->getName()
              );
            } catch (SerializerExceptionInterface $ex) {
              throw new ToolsHelperException(
                'Error denormalizing tool callback parameter: '.$param->getName(),
                previous: $ex
              );
            }
          }
        }
      }
    }
    $ret = call_user_func_array($tool->callback, $paramCallValues);
    if (
      !$ret instanceof Stringable &&
      !is_scalar($ret) &&
      $ret !== null
    ) {
      throw new ToolsHelperException('Tool callback return value has to be "stringable".');
    }
    return $ret;
  }

}
