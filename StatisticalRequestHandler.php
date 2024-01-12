<?php

include 'Request.php';

interface StatisticalRequestHandlerInterface
{
    /**
     * Start the timer for response time
     *
     * @param string $uri The URI of the request endpoint
     */
    public function start(string $uri): void;

    /**
     * Stop the timer for response time
     * Store elapsed time in the data dictionary
     */
    public function finish(): void;

    /**
     * Get the mean response time for a given URI
     *
     * @param string $uri The URI of the request endpoint
     * @return float The mean response time in milliseconds
     */
    public function getMeanResponseTime(string $uri): float;

    /**
     * Get the standard deviation of the response times for a given URI
     *
     * @param string $uri The URI of the request endpoint
     * @return float The standard deviation in milliseconds
     */
    public function getStandardDeviation(string $uri): float;

    /**
     * Get the normalized histogram for a given URI
     * Creates a file named histogram.txt containing the histogram if $graph is true
     *
     * @param string $uri The URI of the request endpoint
     * @return array The normalized histogram with structure:
     *         array["frequencies"] The normalized frequencies of the histogram
     *         array["binEdges"] The bin edges of the histogram
     */
    public function getNormalizedHistogram(string $uri, bool $graph): array;
}


/**
 * Resource request processing class
 * 
 * Instantiations of this class do state based processing of resource requests.
 * To use, instantiate an object and call process() on a URI to get the response
 * data. 
 * This class stores the response times for each URI in a dictionary. It also
 * provides methods to calculate the mean response time, standard deviation, and
 * normalized histogram for a given URI.
 */
class StatisticalRequestHandler extends Request implements StatisticalRequestHandlerInterface
{
    /**
     * The maximum number of bins for the normalized histogram
     * Initialized in the constructor
     */
    private int $maxBins;
    
    /**
     * Dictionary to store response times for each URI
     * 
     * The dictionary has the following structure:
     * array[uri] = array[responseTime1, responseTime2, ...]
     */
    private array $data = array();

    /**
     * The time at which the request was started
     */
    private float $time;

    /**
     * Constructor
     * 
     * @param int $maxBins The maximum number of bins for the normalized histogram
     */
    public function __construct(int $maxBins)
    {
        if (!is_numeric($maxBins) || $maxBins <= 0) {
            throw new InvalidArgumentException("Invalid value for maxBins. Must be a positive numeric value.");
        }
        $this->maxBins = $maxBins;
    }


    /**
     * Start the timer for response time
     *
     * @param string $uri The URI of the request endpoint
     */
    public function start(string $uri): void
    {
        $this->time = microtime(true)* 1000;

        if (!array_key_exists($uri, $this->data)) {
            $this->data[$uri] = array();
        }
    }

    /**
     * Stop the timer for response time
     * Store elapsed time in the data dictionary
     * 
     * @throws LogicException If start() is not called before calling finish()
     */
    public function finish(): void
    {
        if (!isset($this->time) || !isset($this->data[$uri])) {
            throw new LogicException("Call start method before calling finish.");
        }

        $time_elapsed = microtime(true)* 1000 - $this->time;
        $uri = array_key_last($this->data);
        array_push($this->data[$uri], $time_elapsed);
    }

    /**
     * Get the mean response time for a given URI
     * 
     * Time complexity: O(n)
     * Space complexity: O(1)
     * 
     * @param string $uri The URI of the request endpoint
     * @return float The mean response time in milliseconds
     */
    public function getMeanResponseTime(string $uri): float
    {
        if (empty($this->data[$uri] || !array_key_exists($uri, $this->data))) {
            return 0;
        }

        $sum = 0;
        $count = 0;
        foreach ($this->data as $key => $value) {
            if ($key == $uri) {
                $sum = array_sum($value);
                $count = count($value);
            }
        }
        return $sum / $count;
    }

    /**
     * Get the standard deviation of the response times for a given URI
     * 
     * Time complexity: O(n)
     * Space complexity: O(1)
     * 
     * @param string $uri The URI of the request endpoint
     * @return float The standard deviation in milliseconds
     */
    public function getStandardDeviation(string $uri): float
    {
        if (empty($this->data[$uri] || !array_key_exists($uri, $this->data))) {
            return 0;
        }

        $mean = $this->getMeanResponseTime($uri);
        $sum = 0;
        $count = 0;
        foreach ($this->data as $key => $value) {
            if ($key == $uri) {
                $count = count($value);
                foreach ($value as $key => $value) {
                    $sum += pow($value - $mean, 2);
                }
            }
        }
        return sqrt($sum / $count);
    }

    /**
     * Get the normalized histogram for a given URI
     * Creates a file named histogram.txt containing the histogram if $graph is true
     * 
     * Time complexity: O(n)
     * Space complexity: O(n)
     * 
     * @param string $uri The URI of the request endpoint
     * @return array The normalized histogram with structure:
     *         array["frequencies"] The normalized frequencies of the histogram
     *         array["binEdges"] The bin edges of the histogram
     */
    public function getNormalizedHistogram(string $uri, bool $graph): array
    {
        if (empty($this->data[$uri] || !array_key_exists($uri, $this->data))) {
            return [];
        }

        $minTime = min($this->data[$uri]);
        $maxTime = max($this->data[$uri]);

        // Calculate the number of bins based on the Freedman-Diaconis rule
        $iqr = $this->calculateInterquartileRange($this->data[$uri]);
        $targetBinWidth = 2 * $iqr / pow(count($this->data[$uri]), 1/3);
        $numBins = min($this->maxBins, 
                        ceil(($maxTime - $minTime) / $targetBinWidth));

        // Calculate the bin width
        $binWidth = ($maxTime - $minTime) / $numBins;

        $histogram["frequencies"] = array_fill(0, $numBins, 0);
        $normalizedHistogram["binEdges"] = array();

        // Calculate the bin edges
        for ($i = 0; $i < $numBins; $i++) {
            $normalizedHistogram["binEdges"][$i] = $minTime + $i * $binWidth;
        }
        $normalizedHistogram["binEdges"][$numBins] = $maxTime;

        // Calculate the frequencies
        foreach ($this->data[$uri] as $time) {
            $binIndex = (int) floor(($time - $minTime) / $binWidth);
            // Adjust bin index to include the edges
            $binIndex = min($numBins - 1, $binIndex);
            $histogram["frequencies"][$binIndex]++;
        }

        // Normalize the histogram
        $totalObservations = count($this->data[$uri]);
        $normalizedHistogram["frequencies"] = array_map(
            function ($count) use ($totalObservations) {
                return $count / $totalObservations;
            },
            $histogram["frequencies"]
        );

        if ($graph) {
            $this->outputHistogram($normalizedHistogram);
        }
        return $normalizedHistogram;
    }

    /**
     * Calculate the interquartile range of a given data set
     * 
     * @param array $data The data set
     * @return float The interquartile range
     */
    private function calculateInterquartileRange(array $data): float
    {
        sort($data);
        $count = count($data);
        $lowerQuartile = $this->calculatePercentile($data, 0.25);
        $upperQuartile = $this->calculatePercentile($data, 0.75);
        return $upperQuartile - $lowerQuartile;
    }

    /**
     * Calculate the percentile of a given data set
     * 
     * @param array $data The data set
     * @param float $percentile The percentile to calculate
     * @return float The percentile
     */
    private function calculatePercentile(array $data, float $percentile): float
    {
        $count = count($data);
        $index = $percentile * ($count - 1);
        $floor = floor($index);
        $fraction = $index - $floor;

        if (isset($data[$floor]) && isset($data[$floor + 1])) {
            return $data[$floor]+ $fraction * ($data[$floor + 1] - $data[$floor]);
        } else {
            return $data[$floor];
        }
    }

    /**
     * Output a histogram to a file named histogram.txt
     * 
     * @param array $normalizedHistogram The normalized histogram
     */
    private function outputHistogram(array $normalizedHistogram): void
    {
        $file = fopen('histogram.txt', 'w');
        $maxFrequency = max($normalizedHistogram['frequencies']) * 100;

        for ($i = $maxFrequency; $i >= 0; $i--) {
            foreach ($normalizedHistogram['frequencies'] as $value) {
                $frequency = $value * 100;
                fwrite(
                    $file, 
                    ($frequency >= $i) 
                    ? '             *   ' 
                    : '                 ');
            }
            fwrite($file, "\n");
        }

        foreach ($normalizedHistogram['binEdges'] as $binEdge) {
            fwrite($file, $binEdge . ' ');
        }
        fwrite($file, "\n");
        fclose($file);

    }
}

?>