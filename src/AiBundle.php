<?php

namespace AiBundle;

use AiBundle\MCP\Server\Attribute\ContainsMCPTools;
use AiBundle\MCP\Server\DependencyInjection\Compiler\MCPServerPass;
use InvalidArgumentException;
use Reflector;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AiBundle extends AbstractBundle {

  private const OLLAMA_DEFAULT_ENDPOINT = 'http://127.0.0.1:11434';
  private const OPENAI_DEFAULT_ENDPOINT = 'https://api.openai.com/v1';
  private const DEFAULT_TIMEOUT = 300;
  private const DEFAULT_LLM_CONFIG_NAME = 'default';
  private const LLM_DEFINITION_PREFIX = 'ai_bundle.llm.';
  private const MCP_ENDPOINT_DEFINITION_PREFIX = 'ai_bundle.mcp_endpoint.';
  private const MCP_SERVER_DEFINITION = 'ai_bundle.mcp_server';

  private const LLM_CLASS_NAMES = [
    'anthropic' => 'AiBundle\LLM\Anthropic\Anthropic',
    'google_ai' => 'AiBundle\LLM\GoogleAi\GoogleAi',
    'mistral_ai' => 'AiBundle\LLM\MistralAi\MistralAi',
    'ollama' => 'AiBundle\LLM\Ollama\Ollama',
    'open_ai' => 'AiBundle\LLM\OpenAi\OpenAi',
    'deep_seek' => 'AiBundle\LLM\DeepSeek\DeepSeek'
  ];

  public function build(ContainerBuilder $container): void {
    parent::build($container);

    $container->addCompilerPass(new MCPServerPass());
  }

  public function configure(DefinitionConfigurator $definition): void {
    // @phpstan-ignore method.notFound
    $definition->rootNode()
      ->children()
        ->arrayNode('llms')
          ->children()
            ->arrayNode('anthropic')->defaultValue([])
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->stringNode('apikey')->isRequired()->end()
                  ->stringNode('model')->isRequired()->end()
                  ->floatNode('timeout')->defaultValue(self::DEFAULT_TIMEOUT)->end()
                ->end()
              ->end()
            ->end()
            ->arrayNode('google_ai')->defaultValue([])
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->stringNode('apikey')->isRequired()->end()
                  ->stringNode('model')->isRequired()->end()
                  ->floatNode('timeout')->defaultValue(self::DEFAULT_TIMEOUT)->end()
                ->end()
              ->end()
            ->end()
            ->arrayNode('mistral_ai')->defaultValue([])
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->stringNode('apikey')->isRequired()->end()
                  ->stringNode('model')->isRequired()->end()
                  ->floatNode('timeout')->defaultValue(self::DEFAULT_TIMEOUT)->end()
                ->end()
              ->end()
            ->end()
            ->arrayNode('open_ai')->defaultValue([])
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->stringNode('apikey')->isRequired()->end()
                  ->stringNode('model')->isRequired()->end()
                  ->floatNode('timeout')->defaultValue(self::DEFAULT_TIMEOUT)->end()
                  ->stringNode('endpoint')->defaultValue(self::OPENAI_DEFAULT_ENDPOINT)->end()
                ->end()
              ->end()
            ->end()
            ->arrayNode('deep_seek')->defaultValue([])
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->stringNode('apikey')->isRequired()->end()
                  ->stringNode('model')->isRequired()->end()
                  ->floatNode('timeout')->defaultValue(self::DEFAULT_TIMEOUT)->end()
                ->end()
              ->end()
            ->end()
            ->arrayNode('ollama')->defaultValue([])
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->stringNode('endpoint')->defaultValue(self::OLLAMA_DEFAULT_ENDPOINT)->end()
                  ->stringNode('model')->isRequired()->end()
                  ->floatNode('timeout')->defaultValue(self::DEFAULT_TIMEOUT)->end()
                ->end()
              ->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('mcp')->addDefaultsIfNotSet()
          ->children()
            ->arrayNode('server')->addDefaultsIfNotSet()
              ->children()
                ->stringNode('name')->defaultValue('ExampleServer')->end()
                ->stringNode('title')->defaultValue('Example Server Display Name')->end()
                ->stringNode('version')->defaultValue('1.0.0')->end()
                ->stringNode('instructions')->defaultValue('')->end()
              ->end()
            ->end()
            ->arrayNode('endpoints')
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->arrayNode('stdio_transport')
                    ->children()
                      ->arrayNode('command')->isRequired()->stringPrototype()->end()->end()
                      ->stringNode('stop_signal')->defaultValue('SIGINT')->end()
                      ->integerNode('response_timeout')->defaultValue(20)->end()
                    ->end()
                  ->end()
                  ->arrayNode('streamable_http_transport')
                    ->children()
                      ->stringNode('endpoint')->isRequired()->end()
                      ->arrayNode('headers')->stringPrototype()->end()->defaultValue([])->end()
                      ->floatNode('timeout')->defaultValue(30)->end()
                    ->end()
                  ->end()
                ->end()
              ->end()
            ->end()
          ->end()
        ->end()
      ->end();
  }


  /**
   * @param array<mixed> $config
   * @param ContainerConfigurator $container
   * @param ContainerBuilder $builder
   * @return void
   */
  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void{
    $container->import('../config/services.yaml');

    $builder->registerAttributeForAutoconfiguration(
      ContainsMCPTools::class,
      static function (ChildDefinition $definition, ContainsMCPTools $attribute, Reflector $reflector): void {
        $definition->addTag('ai_bundle.server.mcp_tools_collection');
      }
    );

    if (isset($config['llms'])) {
      foreach ($config['llms'] as $llmType => $llmConfigs) {
        foreach ($llmConfigs as $llmConfigName => $llmParameters) {
          $defaultConfig = $llmConfigName === self::DEFAULT_LLM_CONFIG_NAME;
          $definitionId = self::LLM_DEFINITION_PREFIX.$llmType.($defaultConfig ? '' : ('.'.$llmConfigName));
          $builder->setDefinition(
            $definitionId,
            (new Definition(
              self::LLM_CLASS_NAMES[$llmType],
              match ($llmType) {
                'anthropic',
                'google_ai',
                'mistral_ai',
                'deep_seek' => [
                  '$apiKey'   => $llmParameters['apikey'],
                  '$model'    => $llmParameters['model'],
                  '$timeout'  => $llmParameters['timeout']
                ],
                'open_ai' => [
                  '$apiKey'   => $llmParameters['apikey'],
                  '$model'    => $llmParameters['model'],
                  '$timeout'  => $llmParameters['timeout'],
                  '$endpoint' => $llmParameters['endpoint']
                ],
                'ollama' => [
                  '$endpoint' => $llmParameters['endpoint'],
                  '$model'    => $llmParameters['model'],
                  '$timeout'  => $llmParameters['timeout']
                ],
                default => throw new InvalidArgumentException('No definition arguments defined for llm '.$llmType)
              }
            ))->setAutowired(true)
          );
          if ($defaultConfig) {
            $builder->setAlias(self::LLM_CLASS_NAMES[$llmType], $definitionId);
          }
        }
      }
    }

    if (isset($config['mcp']['endpoints'])) {
      foreach ($config['mcp']['endpoints'] as $endpointName => $endpointConfig) {
        $transportDefinitionId = self::MCP_ENDPOINT_DEFINITION_PREFIX.$endpointName.'.transport';

        $builder->setDefinition(
          $transportDefinitionId,
          match (true) {
            isset($endpointConfig['stdio_transport']) => (new Definition(
              'AiBundle\MCP\Client\Transport\StdIoTransport',
              [
                '$command' => $endpointConfig['stdio_transport']['command'],
                '$stopSignal' => constant($endpointConfig['stdio_transport']['stop_signal']),
                '$responseTimeout' => $endpointConfig['stdio_transport']['response_timeout']
              ]
            ))->setAutowired(true),
            isset($endpointConfig['streamable_http_transport']) => (new Definition(
              'AiBundle\MCP\Client\Transport\StreamableHttpTransport',
              [
                '$endpoint' => $endpointConfig['streamable_http_transport']['endpoint'],
                '$headers' => $endpointConfig['streamable_http_transport']['headers'],
                '$timeout' => $endpointConfig['streamable_http_transport']['timeout']
              ]
            ))->setAutowired(true),
            default => throw new InvalidArgumentException(
              'No transport defined for MCP server '.$endpointName
            )
          }
        );
        $builder->setDefinition(
          self::MCP_ENDPOINT_DEFINITION_PREFIX.$endpointName,
          (new Definition(
            'AiBundle\MCP\Client\MCPEndpoint',
            [
              '$transport' => new Reference($transportDefinitionId),
            ]
          ))->setAutowired(true)
        );
      }
    }

    $builder->setParameter(self::MCP_SERVER_DEFINITION, $config['mcp']['server']);
  }


}
