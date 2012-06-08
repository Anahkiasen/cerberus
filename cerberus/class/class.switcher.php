<?php
/**
 * Switcher
 * Small template-ish system
 *
 * @package Cerberus
 */
class Switcher
{
	/**
	 * List of possible designs
	 * @var array
	 */
	private $possible = array();

	/**
	 * Current design
	 * @var string
	 */
	private $actual = null;

	/**
	 * Start up a Switcher instance, with a list of possible designs as arguments
	 */
	public function __construct()
	{
		// Get the list of designs
		$this->possible = func_get_args();

		// Check to see if one is currently selected
		$switchSession = session::get('switch', $this->possible[0]);
		if(isset($switchSession)) $this->current = $switchSession;

		// Define current template
		$this->current = r::get('switch');
		session::set('switch', $this->current);
	}

	/**
	 * Sanitize a request of template
	 *
	 * @param  string $template The wanted template
	 * @return string           Sanitized request
	 */
	private function sanitize($template)
	{
		// If the template is not valid, return current
		if (!in_array($template, $this->possible))
		{
			// If no current, return first available
			$template = $this->current
				? $this->current
				: a::get($this->possible, 0);
		}

		return $template;
	}

	// TODO : Delegate to Dispatch
	// Obtenir le chemin actuel
	public function path($getFolder = 'all')
	{
		$path = dispatch::$assets. '/' .$this->current. '/';
		switch($getFolder)
		{
			case 'css':
				return $path. 'css/';
				break;
			case 'js':
				return $path. 'js/';
				break;
			case 'php':
				return $path. 'php/';
				break;
			case 'img':
				return $path. 'img/';
				break;

			default:
				return $path;
				break;
		}
	}

	/**
	 * Get a piece of content
	 *
	 * @param  string $content The name of a content file
	 * @return string          The filepath of the desired file
	 */
	public function content($content)
	{
		return 'pages/switch-'.$content.'.php';
	}

	/**
	 * Get a list of all possible templates
	 * @return array List of possible templates
	 */
	public function getPossible()
	{
		return $this->possible;
	}

	/**
	 * Get current template
	 * @return string Current template
	 */
	public function getCurrent()
	{
		return $this->current;
	}
}

// Raccourci
function _s($page)
{
	global $switcher;
	return $switcher->content($page);
}
