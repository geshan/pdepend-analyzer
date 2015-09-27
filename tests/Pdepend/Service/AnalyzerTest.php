<?php

namespace tests\Pdepend\Test2Test\Service;

use Geshan\Pdepend\Service\Analyzer;
use SebastianBergmann\PDEPEND\Process\Parser;

/**
 * Test for the Analyzer service class.
 *
 * Class AnalyzerTest
 */
class AnalyzerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Analyzer
     */
    protected $analyzer;

    /**
     * @var string
     */
    protected $fileNameWithPath;

    public function setup()
    {
        parent::setup();

        $this->analyzer = new Analyzer(new Parser());
        $this->fileNameWithPath = __DIR__.'/../Fixtures/summary.xml';
    }

    public function testAnalyzeFailing()
    {
        $methodsExceedingMetricLimit = $this->analyzer->analyze(
            $this->fileNameWithPath,
            [
                Analyzer::CYCLOMATIC_COMPLEXITY_NUMBER => 2,
                Analyzer::NPATH_COMPLEXITY_NUMBER      => 5,
            ]
        );

        $this->checkRootElements($methodsExceedingMetricLimit);

        $methodsExceedingCcn = $methodsExceedingMetricLimit[Analyzer::CYCLOMATIC_COMPLEXITY_NUMBER];
        $methodsExceedingNPath = $methodsExceedingMetricLimit[Analyzer::NPATH_COMPLEXITY_NUMBER];

        $this->assertTrue(is_array($methodsExceedingCcn));
        $this->assertTrue(is_array($methodsExceedingNPath));
        $this->assertEquals(5, count($methodsExceedingCcn));
        $this->assertEquals(1, count($methodsExceedingNPath));

        $methodWithMaximumCcn = array_shift($methodsExceedingCcn);
        $this->checkMetricValuesForMethod($methodWithMaximumCcn, 'Analyzer::analyze', 3);

        $methodWithMaximumNPath = array_shift($methodsExceedingNPath);
        $this->checkMetricValuesForMethod($methodWithMaximumNPath, 'Analyzer::analyze', 6);
    }

    public function testAnalyzePassing()
    {
        $methodsExceedingMetricLimit = $this->analyzer->analyze(
            $this->fileNameWithPath,
            [
                Analyzer::CYCLOMATIC_COMPLEXITY_NUMBER => 3,
                Analyzer::NPATH_COMPLEXITY_NUMBER      => 7,
            ]
        );

        $this->checkRootElements($methodsExceedingMetricLimit);

        $methodsExceedingCcn = $methodsExceedingMetricLimit[Analyzer::CYCLOMATIC_COMPLEXITY_NUMBER];
        $methodsExceedingNPath = $methodsExceedingMetricLimit[Analyzer::NPATH_COMPLEXITY_NUMBER];

        $this->assertTrue(is_array($methodsExceedingCcn));
        $this->assertTrue(is_array($methodsExceedingNPath));
        $this->assertEmpty($methodsExceedingCcn);
        $this->assertEmpty($methodsExceedingNPath);
    }

    protected function checkRootElements(array $methodsExceedingMetricLimit)
    {
        $this->assertEquals(2, count($methodsExceedingMetricLimit));
        $this->assertArrayHasKey(Analyzer::CYCLOMATIC_COMPLEXITY_NUMBER, $methodsExceedingMetricLimit);
        $this->assertArrayHasKey(Analyzer::NPATH_COMPLEXITY_NUMBER, $methodsExceedingMetricLimit);
    }

    protected function checkMetricValuesForMethod(array $method, $methodName, $metricValue)
    {
        $this->assertTrue(is_array($method));
        $this->assertEquals(2, count($method));
        $this->assertEquals($methodName, $method[0]);
        $this->assertEquals($metricValue, $method[1]);
    }
}
