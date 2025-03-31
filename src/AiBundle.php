<?php

namespace AiBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AiBundle extends AbstractBundle {

  private const OLLAMA_DEFAULT_ENDPOINT = 'http://127.0.0.1:11434';
  private const DEFAULT_TIMEOUT = 300;
  private const DEFAULT_LLM_CONFIG_NAME = 'default';
  private const LLM_DEFINITION_PREFIX = 'ai_bundle.llm.';

  private const LLM_CLASS_NAMES = [
    'anthropic' => 'AiBundle\LLM\Anthropic\Anthropic',
    'google_ai' => 'AiBundle\LLM\GoogleAi\GoogleAi',
    'mistral_ai' => 'AiBundle\LLM\MistralAi\MistralAi',
    'ollama' => 'AiBundle\LLM\Ollama\Ollama',
    'open_ai' => 'AiBundle\LLM\OpenAi\OpenAi'
  ];

  public function configure(DefinitionConfigurator $definition): void {
    $definition->rootNode()
      ->children()
        ->arrayNode('llms')->canBeUnset()
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
                ->end()
              ->end()
            ->end()
            ->arrayNode('ollama')->defaultValue([])
              ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->children()
                  ->stringNode('endpoint')->defaultValue('http://127.0.0.1:11434')->end()
                  ->stringNode('model')->isRequired()->end()
                  ->floatNode('timeout')->defaultValue(self::DEFAULT_TIMEOUT)->end()
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

    if (isset($config['llms'])) {
      foreach ($config['llms'] as $llmType => $llmConfigs) {
        foreach ($llmConfigs as $llmConfigName => $llmParameters) {
          $builder->setDefinition(
            self::LLM_DEFINITION_PREFIX.$llmType.($llmConfigName === self::DEFAULT_LLM_CONFIG_NAME ? '' : ('.'.$llmConfigName)),
            (new Definition(
              self::LLM_CLASS_NAMES[$llmType],
              match ($llmType) {
                'anthropic',
                'google_ai',
                'mistral_ai',
                'open_ai' => [
                  '$apiKey'   => $llmParameters['apikey'],
                  '$model'    => $llmParameters['model'],
                  '$timeout'  => $llmParameters['timeout']
                ],
                'ollama' => [
                  '$endpoint' => $llmParameters['endpoint'],
                  '$model'    => $llmParameters['model'],
                  '$timeout'  => $llmParameters['timeout']
                ]
              }
            ))->setAutowired(true)
          );
        }
      }
    }
      
  }


}
