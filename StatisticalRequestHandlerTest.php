<?php

include 'StatisticalRequestHandler.php';

class StatisticalRequestHandlerTest
{
    public function runTests(bool $graph)
    {
        $this->testMeanResponseTime();
        $this->testStandardDeviation();
        $this->testNormalizedHistogram($graph);
    }

    public function testMeanResponseTime()
    {
        $handler = new StatisticalRequestHandler(5);

        // Test for a URI with no response times
        $mean = $handler->getMeanResponseTime('example-uri');
        echo "Test Mean Response Time (Empty): " . ($mean === (float) 0 ? "Passed" : "Failed") . PHP_EOL;
        echo "Mean Response Time: " . $mean . PHP_EOL;

        // Test for a URI with response times
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $mean = $handler->getMeanResponseTime('example-uri');
        echo "Test Mean Response Time (Not Empty): " . ($mean > 0 ? "Passed" : "Failed") . PHP_EOL;
        echo "Mean Response Time: " . $mean . PHP_EOL;
    }

    public function testStandardDeviation()
    {
        $handler = new StatisticalRequestHandler(5);

        // Test for a URI with no response times
        $handler->process('example-uri');
        $stdDev = $handler->getStandardDeviation('example-uri');
        echo "Test Standard Deviation (Empty): " . ($stdDev === (float) 0 ? "Passed" : "Failed") . PHP_EOL;
        echo "Standard Deviation: " . $stdDev . PHP_EOL;

        // Test for a URI with response times
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $stdDev = $handler->getStandardDeviation('example-uri');
        echo "Test Standard Deviation (Not Empty): " . ($stdDev > 0 ? "Passed" : "Failed") . PHP_EOL;
        echo "Standard Deviation: " . $stdDev . PHP_EOL;
    }

    public function testNormalizedHistogram(bool $graph)
    {
        $handler = new StatisticalRequestHandler(5);

        // Test for a URI with no response times
        $histogram = $handler->getNormalizedHistogram('example-uri', false);
        echo "Test Normalized Histogram (Empty): " . (empty($histogram) ? "Passed" : "Failed") . PHP_EOL;

        // Test for a URI with response times
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $handler->process('example-uri');
        $histogram = $handler->getNormalizedHistogram('example-uri', $graph);
        echo "Test Normalized Histogram (Not Empty): " . (!empty($histogram) ? "Passed" : "Failed") . PHP_EOL;
        if ($graph){
            echo "histogram.txt created" . PHP_EOL;
        }
    }
}

// Retrieve command-line argument if available, default to false
$graphArg = isset($argv[1]) ? filter_var($argv[1], FILTER_VALIDATE_BOOLEAN) : false;

$testRunner = new StatisticalRequestHandlerTest();
$testRunner->runTests($graphArg);
?>
