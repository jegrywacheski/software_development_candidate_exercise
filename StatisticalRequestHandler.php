<?php


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

?>