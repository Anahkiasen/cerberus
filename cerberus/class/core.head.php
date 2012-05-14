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
	 */
	private static $head = array();
	
	/**
	 * Order for tags and attributes
	 */
	private static $order_tags = array('base', 'title', 'meta', 'link', 'style');
	
	/**
	 * Adds a tag to the header
	 * @param string    $tag The desired tag
	 * @param array     $attributes An array containing the attributes of the tag
	 */
	static function set($tag, $attributes)
	{
		$tag = array('tag' => $tag);
		$tag = array_merge($tag, $attributes);
		self::$head[] = $tag;
	}
	
	/**
	 * Prints out the current head tag
	 */
	static function header()
	{
		// Setting encoding
		self::set('meta', array('charset' => 'utf-8'));
		
		// Sitemap et CDN
		if(file_exists('sitemap.xml'))   head::set('link', array('rel' => 'sitemap', 'type' => 'application/xml', 'title' => 'Sitemap', 'href' => 'sitemap.xml'));
		if(dispatch::isScript('jquery')) head::set('link', array('rel' => 'dns-prefetch', 'href' => '//ajax.googleapis.com'));;
			
		// Add base tag
		self::baseref();
		
		// Adding META tags
		meta::head();
		
		// Reordering the head tags
		self::reorder();

		// Iterating the head tags
		foreach(self::$head as $id_balise => $attributes)
		{
			// Determine the name and if the tag is self closing
			$balise_name = a::get($attributes, 'tag');
			$self_closing = !isset($attributes['value']);
			$balise = $balise_name;
			
			// Writing the tag attributes
			foreach($attributes as $k => $v)
			{
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
			$balise .= $self_closing ? '/>' : '</'.$balise_name. '>';
			
			// Saving the formatted version
			self::$head[$id_balise] = $balise;
		}
		
		// Prints the head tags
		echo '<head>'.PHP_EOL."\t".implode(PHP_EOL."\t", self::$head).PHP_EOL;
	}
	
	//////////////////////////////////////////////////////////////
	//////////////////////////// MOTEUR ////////////////////////// 
	//////////////////////////////////////////////////////////////
	
	/**
	 * Reorder the tags and attributes in the head tag
	 */
	static function reorder()
	{
		$tags = array();

		// Sorting the head tags by type
		foreach(self::$head as $attributes)
			$tags[a::get($attributes, 'tag')][] = $attributes;
		
		// Emptying the head array
		self::$head = array();
		
		foreach(self::$order_tags as $order)
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
	
	//////////////////////////////////////////////////////////////
	////////////////////////// RACCOURCIS //////////////////////// 
	//////////////////////////////////////////////////////////////
	
	// Set the page title
	static function title($title)
	{
		self::set('title', array('value' => $title));	
	}
	
	// Add a stylesheet
	static function stylesheet($href)
	{
		self::set('link', array('rel' => 'stylesheet', 'href' => $href));
	}
	
	static function css($value)
	{
		self::set('style', array('value' => $value));	
	}
	
	// Add a favicon
	static function favicon($favicon)
	{
		self::set('link', array('rel' => 'shortcut icon', 'href' => PATH_COMMON.'img/'.$favicon));
	}
	
	// Add a base tag to the head
	static function baseref()
	{
		// Baseref
		if(REWRITING and PATH_MAIN == NULL)
		{
			$baseref = LOCAL ? config::get('base.local') : config::get('base.online');
			head::set('base', array('href' => config::get('http').$baseref));
		}
	}
	
}
?>