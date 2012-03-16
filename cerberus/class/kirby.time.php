<?php
class t
{
	/**
	 * Returns a number of seconds to any given format
	 * 
	 * Very similar to the date function to the exeption that the number of seconds doesn't need to be a timestamp
	 * Useful for basic conversions and formating
	 * 
	 * @param int		$secs The number of seconds
	 * @param string 	$format The format to apply, with units placed into brackets, ie. {h}:{m}:{s}
	 * @param boolean	$modulus Wether or not the function returns the total number
	 *                  of any unit, or what's left for each one in ascending order
	 *                  Per example, 90 seconds to the format {m}:{s} will return 01:30 with modulus on TRUE and 01:90 on FALSE
	 * @return string	A formated time string
	 */
	static function format($secs, $format = NULL, $modulus = true) 
	{
		if($modulus)
		{
			$restant = $secs;
			$integers = array('s' => 60, 'i' => 60, 'h' => 24, 'd' => 30.4, 'm' => 12, 'y' => 1); 
	
			foreach($integers as $v => $c)
			{
				$vals[$v] = $v == 'y' ? floor($restant) : $restant % $c;
				$restant -= a::get($vals, $v);
				$restant = $restant / $c;
			}
		}
		else
			$vals = array(
				's' => $secs,
				'i' => $secs / 60,
				'h' => $secs / 60 / 60,
				'd' => $secs / 60 / 60 / 24,
				'w' => $secs / 60 / 60 / 24 / 7,
				'm' => $secs / 60 / 60 / 24 / 30,
				'y' => $secs / 60 / 60 / 24 / 365);
		
		foreach($vals as $type => $time)
			$format = str_replace('{' .$type. '}', str_pad($time, 2, "0", STR_PAD_LEFT), $format);
		
		return $format;
	}
	
	/**
	 * Calculates the different between two dates, in any format (default in days)
	 * 
	 * @param string	$start The beginning date
	 * @param string	$end The ending date
	 * @param string	$pattern The format to apply on the result
	 * @return string	A time difference
	 */
	static function difference($start, $end, $pattern = '{d}')
	{
		$difference = strtotime($end) - strtotime($start);
		return self::format($difference, $pattern, TRUE);
	}
	
	/**
	 * Calculates the exact age (taking into account current month and day) from a birthday
	 * 
	 * @param string	$date A birthday in the format YYYY-MM-DD
	 * @return int		A number of years
	 */
	static function age($date)
	{
 		list($year, $month, $day) = explode('-', $date);
 		
		$yearDiff = date('Y') - $year;
		$monthDiff = date('m') - $month;
		$dayDiff = date('d') - $day;
		
		if( ($monthDiff == 0 and $dayDiff < 0) or ($monthDiff < 0) )
			$yearDiff--;
		
		return $yearDiff;
	}
	
	/**
	 * Shortcut to h:i:s
	 * @param int		$s A number of seconds
	 * @return string	A number of seconds converted to h:i:s
	 */
	static function hms($s)
	{
		return self::format($s, '{h}:{i}:{s}');
	}
	
	/**
	 * Shortcut to i:s
	 * @param int		$s A number of seconds
	 * @return string	A number of seconds converted to i:s
	 */
	static function ms($s)
	{
		return self::format($s, '{i}:{s}');
	}
}
?>