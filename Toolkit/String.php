<?php
namespace Cerberus\Toolkit;

class String
{
	/**
	 * Removes a part of a string
	 * @param  string $delete The part of the string to remove
	 * @param  string $string The string to correct
	 * @return string         The corrected string
	 */
	public static function remove($delete, $string)
	{
		// If we only have one string to remove
		if(!is_array($delete)) return str_replace($delete, null, $string);

		// Else, use Regex
		return preg_replace('#(' .implode('|', $delete). ')#', null, $string);
	}

	/**
	 * Find one or more needles in one or more haystacks
	 *
	 * Also avoid the retarded counter-intuitive original
	 * strpos syntax that makes you put haystack before needle
	 *
	 * @param  mixed   $needle	      The needle(s) to search for
	 * @param  mixed   $haystack      The haystack(s) to search in
	 * @param  boolean $absolute      Whether all needle need to be found or whether one is enough
	 * @param  boolean $caseSensitive Whether the function is case sensitive or not
	 * @return boolean Found or not
	 */
	public static function find($needle, $haystack, $absolute = false, $caseSensitive = false)
	{
		// If several needles
		if(is_array($needle) or is_array($haystack))
		{
			if(is_array($needle))
			{
				$from = $needle;
				$to   = $haystack;
			}
			else
			{
				$from = $haystack;
				$to   = $needle;
			}
			$found = 0;
			foreach($from as $need)
				if(self::find($need, $to, $absolute, $caseSensitive))
					$found++;
			return ($absolute) ? count($from) == $found : $found > 0;
		}
		else
		{
			// If not case sensitive
			if(!$caseSensitive)
			{
				$haystack = strtolower($haystack);
				$needle   = strtolower($needle);
			}

			// If string found
			$pos = strpos($haystack, $needle);
			return !($pos === false);
		}
	}
}