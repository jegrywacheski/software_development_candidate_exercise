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
     *
     * @param string $uri The URI of the request endpoint
     * @return array The normalized histogram with structure:
     *         array["frequencies"] The normalized frequencies of the histogram
     *         array["binEdges"] The bin edges of the histogram
     */
    public function getNormalizedHistogram(string $uri): array;
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
     * 
     * @param string $uri The URI of the request endpoint
     * @return array The normalized histogram
     */
    public function getNormalizedHistogram(string $uri): array
    {
        
    }
}

?>