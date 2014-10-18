<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Geshan\Pdepend\Command\AnalyzerCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$container  = new ContainerBuilder();
$loader     = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('config/services.yml');

$application = new Application('Pdepend Analyzer', '0.1.0');
$application->add(new AnalyzerCommand($container->get('analyzer'), $container->get('logger')));
$application->run();