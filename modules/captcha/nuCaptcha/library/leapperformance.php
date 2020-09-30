<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(class_exists("lmcPerformance", false))
{
	return;
}

require_once "log4php/LoggerManager.php";
require_once "Benchmark/Profiler.php";

/**
 * Performance measurement for web applications
 * 
 * Automatically reports performance at the end of every execution.
 * 
 * See http://intra.leapmarketing.com/confluence/display/TD/lmPerformance for 
 * design and reference docs.
 *
 */
class lmcPerformance
{
	private $mBenchmark;
	private $mLogger;
	
	/**
	 * Create a new performance logging object
	 *
	 * @param string $loggerName
	 * @param boolean $report
	 * @return boolean
	 */
	public function __construct	($loggerName, $report = true)
	{
		// set the logger object
		$this->mLogger = Logger::getLogger($loggerName);
		
		// set the benchmark object
		$this->mBenchmark = new Benchmark_Profiler();
		
		// start benchmarking
		$this->getBenchmark()->start();
		
		return true;
	}
	
	public function __destruct ()
	{
		// stop logging
		$this->getBenchmark()->stop();
		
		// log output 
		return $this->LogOutput();
	}
	
	/**
	 * Start profiling a section
	 *
	 * @param string $name Name of section
	 * @return bool
	 */
	public function enterSection ($name)
	{
		$this->getBenchmark()->enterSection($name);
		
		return true;
	}
	
	/**
	 * Stop profiling a section
	 *
	 * @param string $name Name of section
	 * @return bool
	 */
	public function leaveSection ($name)
	{
		$this->getBenchmark()->leaveSection($name);
		
		return true;
	}
	
	/**
	 * Returns an HTML formatted table of the current performance info
	 *
	 * @return string
	 */
	public function GetPerformanceHtml ()
	{
		ob_start();
		
		$this->getBenchmark()->display('html');
		
		return ob_get_clean();
	}
	
	/**
	 * return the benchmark object
	 *
	 * @return Benchmark_Profiler
	 */
	private function getBenchmark () 
	{
		return $this->mBenchmark;
	}
	
	/**
	 * return the log4php object
	 *
	 * @return Logger
	 */
	private function getLogger ()
	{
		return $this->mLogger;
	}
	
	/**
	 * log output to log4php
	 * 
	 * @return bool
	 *
	 */
	private function LogOutput ()
	{
		$logger = $this->getLogger();
		$b      = $this->getBenchmark();
		
		// record profiling data and log it
		$prof = $b->getAllSectionsInformations();
		
		foreach (array_keys($prof) AS $mark)
		{
			$logger->info(sprintf(
				'lmPerformance: %s, Time: %f, Percentage: %f%%, Total Calls: %d, Average: %f',
					$mark,
					$prof[$mark]['time'],
					$prof[$mark]['percentage'],
					$prof[$mark]['num_calls'],
					$prof[$mark]['time'] / $prof[$mark]['num_calls']
			));
		}
	}
}

