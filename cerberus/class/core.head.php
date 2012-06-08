<?php
/**
 *
 * Head
 *
 * This class handles the generation and managing of the head tag
 *
 * @package Cerberus
 */
class head
{
	/**
	 * The main array containing the head tags
	 * @var array
	 */
	private static $head = array();

	/**
	 * Order for tags and attributes
	 * @var array
	 */
	private static $orderTags = array('base', 'title', 'meta', 'link', 'style');

	/**
	 * Adds a tag to the header
	 *
	 * @param string    $tag        The desired tag
	 * @param array     $attributes An array containing the attributes of the tag
	 */
	public static function set($tag, $attributes)
	{
		$tag = array('tag' => $tag);
		$tag = array_merge($tag, $attributes);
		self::$head[] = $tag;
	}

	/**
	 * Get the current array
	 *
	 * @return array The current header
	 */
	public static function get()
	{
		return self::$head;
	}

	/**
	 * Prints out the current head tag
	 */
	public static function header()
	{
		// Setting encoding
		self::set('meta', array('charset' => 'utf-8'));

		// Sitemap et CDN
		if(file_exists('sitemap.xml'))
			self::set('link', array(
				'rel' => 'sitemap',
				'type' => 'application/xml',
				'title' => 'Sitemap',
				'href' => 'sitemap.xml'));
		if(dispatch::isScript('jquery'))
			self::set('link', array(
				'rel' => 'dns-prefetch',
				'href' => '//ajax.googleapis.com'));

		// Add base tag
		self::baseref();

		// Adding META tags
		meta::head();

		// Reordering the head tags
		self::reorder();

		// Iterating the head tags
		foreach(self::$head as $idBalise => $attributes)
		{
			// Determine the name and if the tag is self closing
			$baliseName = a::get($attributes, 'tag');
			$selfClosing = !isset($attributes['value']);
			$balise = $baliseName;

			// Writing the tag attributes
			foreach($attributes as $k => $v)
			{
				// Remove META placeholders
				$v = str::remove('{meta} - ', $v);

				// Non self closing tags
				if($k == 'value')
				{
					$balise .= '>' .$v;
					continue;
				}

				if($k == 'tag') continue;
				else $balise .= ' ' .$k. '=\'' .addslashes($v). '\'';
			}

			// Wrapping the tag
			$balise = '<'.$balise;
			$balise .= $selfClosing ? '/>' : '</'.$baliseName. '>';

			// Saving the formatted version
			self::$head[$idBalise] = $balise;
		}

		// Prints the head tags
		echo '<head>'.PHP_EOL."\t".implode(PHP_EOL."\t", self::$head).PHP_EOL;
	}

	//////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Reorder the tags and attributes in the head tag
	 */
	private static function reorder()
	{
		$tags = array();

		// Sorting the head tags by type
		foreach(self::$head as $attributes)
			$tags[a::get($attributes, 'tag')][] = $attributes;

		// Emptying the head array
		self::$head = array();

		foreach(self::$orderTags as $order)
		{
			if(!isset($tags[$order])) continue;

			// Ordering link tags by rel attribute
			//if($order == 'link') $tags[$order] = a::sort($tags[$order], 'rel', 'asc');

			// Reinserting the tags in the head
			foreach($tags[$order] as $attributes)
			{
				ksort($attributes);
				self::$head[] = $attributes;
			}
		}

	}

	//////////////////////////////////////////////////////////////////
	//////////////////////////// SHORTCUTS ///////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Set the current page title
	 *
	 * @param  string  $title  The page title
	 */
	public static function title($title)
	{
		self::set('title', array('value' => $title));
	}

	/**
	 * Add a stylesheet to the page
	 *
	 * @param  string  $href  Link or path to the stylesheet
	 */
	public static function stylesheet($href)
	{
		self::set('link', array('rel' => 'stylesheet', 'href' => $href));
	}

	/**
	 * Add CSS styles to the page
	 *
	 * @param  string  $value  CSS code
	 */
	public static function css($value)
	{
		self::set('style', array('value' => $value));
	}

	/**
	 * Add a favicon to the page
	 *
	 * @param  string  $favicon  Name of the favicon file (must be in image folder)
	 */
	public static function favicon($favicon = 'favicon.png')
	{
		self::set('link', array('rel' => 'shortcut icon', 'href' => PATH_COMMON.'img/'.$favicon));
	}

	/**
	 * Adds mobile responsive capability to a webpage
	 */
	public static function mobile()
	{
		self::set('meta', array(
			'name' => 'apple-mobile-web-app-capable',
			'content' => 'yes'));
		self::set('meta', array(
			'name' => 'apple-touch-fullscreen',
			'content' => 'yes'));
		self::set('meta', array(
			'name' => 'viewport',
			'content' => 'width = device-width, initial-scale = 1, user-scalable = no'));
	}

	/**
	 * Add a base tag to the page
	 */
	public static function baseref()
	{
		if(REWRITING and PATH_MAIN == null)
		{
			$baseref = LOCAL ? config::get('base.local') : config::get('base.online');
			self::set('base', array('href' => config::get('http').$baseref));
		}
	}
}
