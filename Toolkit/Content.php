<?php
/**
 *
 * Content
 *
 * This class handles output buffering,
 * content loading and setting content type headers.
 *
 * @package Kirby
 */
namespace Cerberus\Toolkit;

class Content
{
	/**
	 * Starts the output buffer
	 *
	 */
	public static function start()
	{
		ob_start();
	}

	/**
	 * Stops the output buffer and flush the content or return it.
	 *
	 * @param  boolean $return Pass true to return the content instead of flushing it
	 * @return mixed
	 */
	public static function end($return = false)
	{
		if($return)
		{
			$content = ob_get_clean();
			return $content;
		}
		ob_end_flush();
	}

	/**
	 * Shortcut for content::end(true)
	 *
	 * @return string Content of the output buffer
	 */
	public static function get()
	{
		return trim(self::end(true));
	}

	/**
	 * Loads content from a passed file
	 *
	 * @param  string  $file   The path to the file
	 * @param  boolean $return True: return the content of the file, false: echo the content
	 * @return mixed
	 */
	public static function load($file, $return = true)
	{
		if(!file_exists($file)) return false;

		self::start();
			require_once($file);
		$content = self::get();

		if($return) return $content;
		echo $content;
	}
}
