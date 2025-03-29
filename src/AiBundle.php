<?php

namespace AiBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AiBundle extends AbstractBundle {

  /**
   * @param array<mixed> $config
   * @param ContainerConfigurator $container
   * @param ContainerBuilder $builder
   * @return void
   */
  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void{
    $container->import('../config/services.yaml');
  }

}
