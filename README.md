# Request Handler Exercise

Completed as an exercise for goodmorning.com
All content produced solely by Jesse Grywacheski, with the exception of Request.php

## StatisticalRequestHandler

The StatisticalRequestHandler class extends the functionality of the Request class, offering additional methods to facilitate the retrieval and analysis of statistical data related to response times for processed URIs. This enhanced class meticulously maintains a structured record of all response times.

### Mean Response Time

The child class now incorporates a method for retrieving the mean response time. This addition enables users to obtain the average response time for a specific URI from the stored data.

### Standard Deviation

The child class now includes a method for calculating the standard deviation of response times. This enhancement facilitates a more in-depth analysis of the variability in data, allowing users to understand the spread of response times for a specific URI.

### Normalized Histogram

The child class introduces a method for generating normalized histograms based on response times. This new functionality allows users to visualize the distribution of response times for a particular URI, providing valuable insights into the patterns and frequencies within the dataset.
The histogram is created using the Freedman-Diaconis rule and provides information on the bin edges and frequencies and includes the option to 
create a text file graph.

#### Freedman-Diaconis Rule

The Freedman-Diaconis rule is a method for determining the number of bins in a histogram. It is designed to provide a reasonable estimate for the bin width in a histogram, taking into account the spread and skewness of the data.

The formula for calculating the bin width according to the Freedman-Diaconis rule is:
$$
\text{Bin Width} = 2 \times \frac{\text{Interquartile Range}}{\sqrt[3]{\text{Number of Data Points}}}
$$

The interquartile range is a measure of statistical dispersion, representing the range between the first quartile (25th percentile) and the third quartile (75th percentile) of the dataset.


### Testing 

To execute the testing script via command line:

``` php StatisticalRequestHandlerTest.php [graph_flag] ```

The [graph_flag] argument is optional and can be either true or false. It determines whether a histogram graph is generated. If not provided, it defaults to false.