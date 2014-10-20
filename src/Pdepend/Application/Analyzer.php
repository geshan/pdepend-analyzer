<?php

namespace Geshan\Pdepend\Application;

use Geshan\Pdepend\Command\AnalyzerCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Analyzer extends Application
{
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'pdepend-analyze';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $defaultCommands   = parent::getDefaultCommands();
        $defaultCommands[] = $this->getAnalyzerCommand();

        return $defaultCommands;
    }

    protected function getAnalyzerCommand()
    {
        $container  = new ContainerBuilder();
        $loader     = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('Config/services.yml');

        return new AnalyzerCommand($container->get('analyzer'), $container->get('logger'));
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }

} 