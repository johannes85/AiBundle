<?php

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once(__DIR__ . '/../vendor/autoload.php');

$container = new ContainerBuilder();
$container->register(LoggerInterface::class)
  ->setClass(ConsoleLogger::class)
  ->setArgument('$output', new ConsoleOutput());
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');
$loader->load('services_examples.yaml');
$container->compile();

$app = new Application('Symfony AiBundle examples');
$app->addCommands([
  $container->get(\Johannes85\AiBundle\LLM\Ollama\Examples\GenerateCommand::class),
]);
$app->run();
