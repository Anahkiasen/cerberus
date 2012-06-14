<?php
/**
 *
 * Timer
 *
 * Measure time between stuff
 * @package Cerberus
 *
 */
class Timer
{
	/**
	 * A list of timers started
	 * @var array
	 */
	public static $running  = array();

	/**
	 * A list of timers stopped and saved
	 * @var array
	 */
	public static $finished = array();

	/**
	 * Start a timer
	 *
	 * @param  string $key The name of the timer
	 */
	public static function start($key = null)
	{
		// Get current timestamp in microseconds
		$microtime = microtime(true);

		// Save this as starting time
		if($key) self::$running[$key] = $microtime;
		else     self::$running[]     = $microtime;
	}

	/**
	 * Stops a timer
	 *
	 * @param  string $key The name of the timer, if none given, last one assumed
	 */
	public static function save($key = null)
	{
		// If no key given, use last one
		if(!$key)
		{
			$keys = array_keys(self::$running);
			$key  = end($keys);
		}

		// Get time
		$time = self::getRunning($key);

		// If nothing to save, quit
		if(!$time) return false;

		// Save elapsed time
		self::$finished[$key] = microtime(true) - $time;
		self::$running        = a::remove(self::$running, $key);
	}

	/**
	 * Get running timer
	 *
	 * @param  string $key A timer
	 * @return int         A time
	 */
	public function getRunning($key = null)
	{
		return self::get('running', $key);
	}

	/**
	 * Get finished timer
	 *
	 * @param  string $key A timer
	 * @return int         A time
	 */
	public static function getFinished($key = null)
	{
		return self::get('finished', $key);
	}

	/**
	 * Save all remaining timers and calculate total time
	 *
	 * @return array The time of each timer
	 */
	public static function close()
	{
		// Save any timer still running
		foreach(self::$running as $k => $v)
			self::save($k);

		// Calculate total time
		self::$finished['total'] = array_sum(self::$finished);

		// Convert times to seconds
		foreach(self::$finished as $k => $v)
			self::$finished[$k] = self::toSeconds($v);

		return self::$finished;
	}

	/**
	 * Display all saved timers
	 */
	public static function show()
	{
		if(!empty(self::$running)) self::close();

		$total = self::$finished['total'];
		echo '<pre>';
			foreach(self::$finished as $name => $time)
			{
				$percentageTime = round($time * 100 / $total, 0);
				echo
					$name.
					self::pad($name).
					$time.
					self::pad($time).
					$percentageTime.
					'%<br/>';
			}
		echo '</pre>';
	}

	//////////////////////////////////////////////////////////////////
	/////////////////////////// HELPERS //////////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Create an artifical table
	 *
	 * @param  string $string A string
	 * @return string         Space required to align to next column
	 */
	private static function pad($string)
	{
		$length = strlen($string);
		return str_repeat(
			' ',
			20 - $length);
	}

	/**
	 * Fetch one/all the saved times
	 *
	 * @param  string $key The name of a timer, if none, all assumed
	 * @return mixed       Array if all, a time if specific timer asked
	 */
	private static function get($table = 'running', $key = null)
	{
		if(!$key) return self::${$table};
		else      return a::get(self::${$table}, $key);
	}

	/**
	 * Microseconds to seconds
	 *
	 * @param  int $time A time in microseconds
	 * @return int       A time in seconds
	 */
	private static function toSeconds($time)
	{
		return round($time * 1000, 2);
	}
}

Timer::start();