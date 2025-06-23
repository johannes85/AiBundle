<?php

namespace AiBundle\MCP\Server\DependencyInjection\Compiler;

use AiBundle\Json\SchemaGenerator;
use AiBundle\MCP\Server\Attribute\MCPTool;
use AiBundle\MCP\Server\ToolRegistry;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MCPServerPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container) {
    $mcpToolCollections = $container->findTaggedServiceIds('ai_bundle.server.mcp_tools_collection');
    $toolRegistryDefinition = $container->getDefinition(ToolRegistry::class);
    $schemaGenerator = new SchemaGenerator();
    foreach ($mcpToolCollections as $serviceId => $tags) {
      $container->getDefinition($serviceId)->setPublic(true);
      foreach ($container->getReflectionClass($serviceId)->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $mcpToolAttribute = ($method->getAttributes(MCPTool::class)[0] ?? null)?->newInstance();
        if ($mcpToolAttribute !== null) {
          $name = $mcpToolAttribute->name ?? $method->getName();
          $toolRegistryDefinition->addMethodCall('registerTool', [
            $serviceId,
            $method->getName(),
            $name,
            $mcpToolAttribute->description ?? '',
            $schemaGenerator->generateForFunctionParameters($method)
          ]);
        }
      }
    }
  }

}
