<?php

namespace AiBundle\Prompting\Tools;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use InvalidArgumentException;
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
    #[Autowire('@ai_bundle.rest.serializer')] private readonly Serializer $serializer
  ) {}

  /**
   * Returns schema of tool callback parameters as object
   *
   * @param Tool $tool
   * @return array<mixed>
   * @throws ToolsHelperException
   */
  public function getToolCallbackSchema(Tool $tool): array {
    try {
      return $this->schemaGenerator->generateForClosureParameters($tool->callback);
    } catch (ReflectionException $ex) {
      throw new ToolsHelperException('Error getting tool callback parameters via reflection', previous: $ex);
    } catch (SchemaGeneratorException $ex) {
      throw new ToolsHelperException('Error generating schema for tool callback', previous: $ex);
    }
  }

  /**
   * Calls tool with arguments string returned by LLM, matching the tool callback schema
   *
   * @param Tool $tool
   * @param string|array<mixed> $arguments
   * @return mixed
   * @throws ReflectionException
   * @throws ToolsHelperException
   */
  public function callTool(Tool $tool, string|array $arguments): mixed {
    if (is_string($arguments)) {
      $arguments = json_decode($arguments, true);
    }
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
