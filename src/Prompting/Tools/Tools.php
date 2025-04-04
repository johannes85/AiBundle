<?php

namespace AiBundle\Prompting\Tools;

use AiBundle\Json\SchemaGenerator;
use AiBundle\Json\SchemaGeneratorException;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Serializer;

class Tools {

  public function __construct(
    private readonly SchemaGenerator $schemaGenerator,
    #[Autowire('@ai_bundle.rest.serializer')] private readonly Serializer $serializer
  ) {}

  /**
   * Returns schema of
   *
   * @param Tool $tool
   * @return array<mixed>
   * @throws ToolsException
   */
  public function getToolCallbackSchema(Tool $tool): array {
    try {
      $callbackParameterSchemas = $this->schemaGenerator->generateForClosureParameters(
        $tool->callback
      );
      if (($paramCount = count($callbackParameterSchemas)) !== 1) {
        throw new InvalidArgumentException(sprintf(
          'Callback for tool %s has an invalid number of parameters: %d expected: 1',
          $tool->name,
          $paramCount
        ));
      }
      return array_pop($callbackParameterSchemas);
    } catch (ReflectionException $ex) {
      throw new ToolsException('Error getting tool callback parameters via reflection', previous: $ex);
    } catch (SchemaGeneratorException $ex) {
      throw new ToolsException('Error generating schema for tool callback', previous: $ex);
    }
  }

  public function callTool(Tool $tool, string $arguments): mixed {
    $params = (new ReflectionFunction($tool->callback))->getParameters();

    $data = $this->serializer->deserialize($arguments, $params[0]->getType()->getname(), 'json');
    $callback = $tool->callback;

    return $callback($data);
  }

}
