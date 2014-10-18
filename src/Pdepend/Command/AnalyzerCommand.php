<?php

namespace Geshan\Pdepend\Command;

use Geshan\Pdepend\Service\Analyzer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzerCommand extends Command
{
    protected $analyzer;
    protected $logger;

    const MESSAGE_PASSED              = 'Hurray! There are no methods/functions which exceed ccn or npath limits';
    const MESSAGE_FAILED              = 'There are method(s) which exceed ccn or npath limits';
    const MESSAGE_METRIC_NOT_EXCEEDED = 'There is no method exceeding %s limit.';
    const MESSAGE_METRIC_EXCEEDED     = 'There are method(s) exceeding %s limit.';

    public function __construct(Analyzer $analyzer, LoggerInterface $logger)
    {
        parent::__construct('Summary Analyzer');

        $this->analyzer= $analyzer;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('pdepend:analyze')
            ->setDescription('Parse summary.xml provided by Pdepend to check cyclomatic complexity and N-Path complexity.')
            ->setHelp(<<<EOT
'Needs a summary.xml fie generated by Pdepend to check reported cyclomatic complexity and N-Path complexity'
EOT
            )
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'needs the path of the summary.xml file')
            ->addOption('cyclomatic-complexity-limit', null, InputOption::VALUE_OPTIONAL, 'cyclomatic complexity number limit')
            ->addOption('npath-complexity-limit', null, InputOption::VALUE_OPTIONAL, 'npath complexity number limit');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filenameWithPath = $input->getOption('file');
        $exit             = 1;

        if($filenameWithPath === null) {
            $output->writeln('<error>File not found</error>');
            exit(2);
        }

        $output->writeln(sprintf('Executing pdepend summary analyze command with file <info>%s</info>', $filenameWithPath));
        $output->writeln(sprintf('<comment>ccn = Cyclomatic Complexity Number</comment>'));

        $methodsExceedingMetricLimit = $this->analyzer->analyze(
             $filenameWithPath,
             [
                Analyzer::CYCLOMATIC_COMPLEXITY_NUMBER => $input->getOption('cyclomatic-complexity-limit'),
                Analyzer::NPATH_COMPLEXITY_NUMBER      => $input->getOption('npath-complexity-limit')
             ]
        );

        if ($this->analyzer->checkPassed($methodsExceedingMetricLimit)) {
            $exit = 0;
            $output->writeln(sprintf('<info>%s</info>', self::MESSAGE_PASSED));

        } else {
            $output->writeln(sprintf('<error>%s</error>', self::MESSAGE_FAILED));
            $this->showResultsAsTable($output, $methodsExceedingMetricLimit);
        }

        $output->writeln(sprintf('<info>Analyzer command done</info>'));
        exit($exit);
    }

    protected function showResultsAsTable(OutputInterface $output, array $methodsExceedingMetricLimit)
    {
        foreach ($methodsExceedingMetricLimit as $metricName => $methods) {
            if (count($methods) === 0) {
                $message = sprintf(self::MESSAGE_METRIC_NOT_EXCEEDED, $metricName);
                $output->writeln(sprintf('<info>%s</info>', $message));
            } else {
                $message = sprintf(self::MESSAGE_METRIC_EXCEEDED, $metricName);
                $output->writeln(sprintf('<error>%s</error>', $message));

                $table   = new Table($output);
                $table->setHeaders(array('No.', 'Method Name', sprintf('%s metric', $metricName)));
                $this->addMethodMetricsAsRows($table, $methods);
                $table->render();
            }
        }
    }

    protected function addMethodMetricsAsRows(Table $table, array $methods)
    {
        $counter = 1;

        foreach($methods as $methodRow) {
            array_unshift($methodRow, $counter);
            $table->addRow($methodRow);
            $counter++;
        }
    }
}
