<?php

namespace Geshan\Pdepend\Service;

use SebastianBergmann\PDEPEND\Process\Parser;

class Analyzer
{
    const CYCLOMATIC_COMPLEXITY_NUMBER = 'ccn';
    const NPATH_COMPLEXITY_NUMBER      = 'npath';

    const CYCLOMATIC_COMPLEXITY_LIMIT = 10;
    const NPATH_COMPLEXITY_LIMIT      = 200;

    /**
     * @var \SebastianBergmann\PDEPEND\Process\Parser
     */
    protected $parser;

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Analyzes the given Pdepend summary xml file and checks if the methods exceed the passed or default metric limit
     * for Cyclomatic Complexity (ccn) or N-Path Complexity (npath)
     *
     * @param       $filenameWithPath
     * @param array $metricsToAnalyze
     *
     * @return array
     */
    public function analyze($filenameWithPath, array $metricsToAnalyze)
    {
        $codeMetricsData             = $this->parser->parse($filenameWithPath);
        $defaultLimits               = $this->getDefaultLimits();
        $methodsExceedingMetricLimit = [];

        foreach ($metricsToAnalyze as $metricName => $metricLimit) {
            $methodsExceedingMetricLimit[$metricName] = $this->getMethodNamesExceedingMetricLimit(
                $codeMetricsData,
                $metricName,
                ($metricLimit) ? $metricLimit : $defaultLimits[$metricName]
            );
        }

        return $methodsExceedingMetricLimit;
    }

    /**
     * Checks if the checking passed or failed, if any key in the $methodsExceedingMetricLimit has records it is a
     * failure and the code needs some form of refactoring, else it passed.
     *
     * @param array $methodsExceedingMetricLimit
     *
     * @return bool
     */
    public function checkPassed(array $methodsExceedingMetricLimit)
    {
        foreach ($methodsExceedingMetricLimit as $methods) {
            if (count($methods) > 0) {
                return false;
            }
        }

        return true;
    }

    protected function getDefaultLimits()
    {
        return [
            self::CYCLOMATIC_COMPLEXITY_NUMBER => self::CYCLOMATIC_COMPLEXITY_LIMIT,
            self::NPATH_COMPLEXITY_NUMBER      => self::NPATH_COMPLEXITY_LIMIT
        ];
    }

    protected function getMetric(array $codeMetricsData, $metricName)
    {
        return (array_key_exists($metricName, $codeMetricsData)) ? $codeMetricsData[$metricName] : null;
    }

    protected function getMethodNamesExceedingMetricLimit(array $codeMetricsData, $metricName, $metricLimit)
    {
        $metricWithNumbers               = $this->getMetric($codeMetricsData, $metricName);
        $methodNamesExceedingMetricLimit = [];

        foreach ($metricWithNumbers as $methodName => $metricValue) {
            if ($metricValue > $metricLimit) {
                $methodNamesExceedingMetricLimit[] = [$methodName, $metricValue];
            }
        }

        return $methodNamesExceedingMetricLimit;
    }
}
