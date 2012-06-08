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
class content
{
	private static $cache_folder = PATH_CACHE;
	private static $cachename = null;

	/**
		* Starts the output buffer
		*
		*/
	public static function start()
	{
		ob_start();
	}

	/**
		* Stops the output buffer
		* and flush the content or return it.
		*
		* @param	boolean	$return Pass true to return the content instead of flushing it
		* @return mixed
		*/
	public static function end($return = false)
	{
		if($return)
		{
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		ob_end_flush();
	}

	/**
		* Loads content from a passed file
		*
		* @param	string	$file The path to the file
		* @param	boolean $return True: return the content of the file, false: echo the content
		* @return mixed
		*/
	public static function load($file, $return = true)
	{
		self::start();
		require_once($file);
		$content = self::end(true);
		if($return) return $content;
		echo $content;
	}

	/**
		* Simplifies setting content type headers
		*
		* @param	string	$ctype The shortcut for the content type. See the keys of the $ctypes array for all available shortcuts
		* @param	string	$charset The charset definition for the content type header. Default is "utf-8"
		*/
	public static function type()
	{
		$args = func_get_args();

		// shortcuts for content types
		$ctypes = array(
			'html' => 'text/html',
			'css'	=> 'text/css',
			'js'	 => 'text/javascript',
			'jpg'	=> 'image/jpeg',
			'png'	=> 'image/png',
			'gif'	=> 'image/gif',
			'json' => 'application/json'
		);

		$ctype	 = a::get($args, 0, c::get('content_type', 'text/html'));
		$ctype	 = a::get($ctypes, $ctype, $ctype);
		$charset = a::get($args, 1, c::get('charset', 'utf-8'));

		header('Content-type: ' . $ctype . '; charset=' . $charset);

	}
}
