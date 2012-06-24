<?php
/**
 *
 * Navigation
 *
 * Manages navigation trees, menus and current page
 *
 * @package Cerberus
 */
class navigation
{
	// Options ----------------------------------------------------- /

	/**
	 * Whether the main navigation should be list-based or not
	 * @var boolean
	 */
	static private $optionListed     = false;

	/**
	 * Whether the sub navigation should be list-based or not
	 * @var boolean
	 */
	static private $optionListedSub  = false;

	/**
	 * A glue to insert between each element of the menu
	 * @var string
	 */
	static private $optionGlue       = null;

	/**
	 * A glue to insert between each element of the submenu
	 * @var string
	 */
	static private $optionGlueSub    = null;

	// Current environnement --------------------------------------- /

	/**
	 * The website's homepage, used as default in case of error
	 * @var string
	 */
	static private $homepage         = null;

	/**
	 * The current page
	 * @var string
	 */
	static public  $page             = null;

	/**
	 * The current subpage
	 * @var string
	 */
	static public  $sousPage         = null;

	/**
	 * The actual name of the page's file
	 * @var string
	 */
	static private $filepath         = null;

	/**
	 * A list of system pages that are in the tree but used as functions
	 * @var array
	 */
	static private $system           = array('404', 'sitemap');

	// Render ------------------------------------------------------ /

	/**
	 * HTML rendered version of the main tree
	 * @var array
	 */
	static private $renderNavigation = array();

	/**
	 * HTML rendered version of the sub tree
	 * @var array
	 */
	static private $renderSubnav     = array();

	// Private data ------------------------------------------------ /

	/**
	 * The main navigation tree
	 * @var array
	 */
	static private $data             = array();

	/**
	 * The name of the folder containing page files
	 * @var string
	 */
	static private $folder           = 'pages/';

	//////////////////////////////////////////////////////////////////
	///////////////////////// NAVIGATION SETUP ///////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Initiate the navigation core
	 */
	public function __construct()
	{

		// Creating the navigation tree ---------------------------- /

		// Search for existing navigation tree in the cache
		$data = cache::fetch('navigation');
		if($data) self::$data = $data;

		// If no navigation tree found, try creating it from raw data
		if(!self::$data)
		{
			// From the database
			if(SQL and db::is_table('cerberus_structure'))
				$dataRaw = db::select('cerberus_structure', '*', null, 'parent_priority ASC, page_priority ASC');

			// From files
			else
			{
				// Search the config file for a navigation tree, otherwise just use a default tree
				$navigation = config::get('navigation');
				if(!$navigation) $navigation = array('home');

				// Rearrange the raw tree to conform it to the base structure
				foreach($navigation as $page) $dataRaw[] = self::addPage($page);
			}

			// Build the navigation tree from raw data
			self::$data = self::build($dataRaw);
		}

		// Current page -------------------------------------------- /

		// Get the current page and subpage
		self::whatPage();

		// Mark those pages as "active" in the navigations
		self::active();

	}

	/**
	 * Analyzes the GET variables, available files etc. to determine current page
	 */
	private static function whatPage()
	{
		// Set first page in tree as homepage
		self::$homepage = key(self::$data);

		// Calculating current page
		$page = isset(self::$data[r::get('page')]) ? r::get('page') : self::$homepage;

		// If we are in the admin, subpage is admin variable
		if($page == 'admin' and !r::get('pageSub')) $_GET['pageSub'] = r::get('admin');

		// Calculating subpage
		$pageSubmenu = a::get(self::$data, $page.',submenu');
		if($pageSubmenu) reset($pageSubmenu);
		$sousPage = ($pageSubmenu)
			? a::get($pageSubmenu, r::get('pageSub'))
				? r::get('pageSub')
				: key($pageSubmenu)
			: null;

		// If we're not in any particular case, fetch the file path
		if(!in_array($page, self::$system) and $page != 'admin')
			self::$filepath = self::extension($page, $sousPage);

		// Save the calculated data
		self::$page     = $page;
		self::$sousPage = $sousPage;
	}

	//////////////////////////////////////////////////////////////////
	//////////////////////////// CONTENT /////////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Go fetch the content related to the current page
	 */
	public static function content($include = false)
	{
		if(!self::$page) return false;

		$filepath = null;
		switch(self::$page)
		{
			case '404';
				$filepath = PATH_CORE.'include/404.php';
				break;

			case 'sitemap':
				$filepath = PATH_CORE.'include/sitemap.php';
				break;

			case 'admin':
				new Admin_Setup();
				break;

			default:
				if(self::$filepath and file_exists(self::$folder.self::$filepath)) $filepath = self::$folder.self::$filepath;
				else
				{
					$error = str_replace('{filepath}', self::$filepath. ' [' .self::$page. ']', l::get('error.filepath'));
					str::display($error, 'error');
					errorHandle('Warning', $error, __FILE__, __LINE__);
				}
				break;
		}

		// Return the found filepath
		if($filepath and $include) include $filepath;
		else return $filepath;
	}

	/**
	 * Creates a breadcrumb navigation
	 *
	 * @param  string $home  Set text for homepage link
	 * @param  array  $links Supplementary links to append to the navigation, formatted as TEXT => LINK
	 * @return string        A breadcrumb navigation
	 */
	public static function ariane($home = null, $links = array())
	{
		// Get the site name from the config file and make it a link
		$home = config::get('sitename', $home);
		$home = array($home => 'index.php');

		// Initial array
		$crumbs = array(
			l::get('menu-'.self::$page) => url::rewrite(self::$page),
			l::get('menu-'.self::$page.'-'.self::$sousPage) => url::rewrite(self::$page.'-'.self::$sousPage)
			);
		if($home) $crumbs = array_merge($home, $crubs);
		if($links) $crumbs = array_merge($crumbs, $links);

		// Creating the breadcrumbs
		$breadcrumbs = '<ul class="breadcrumb">';
			foreach($crumbs as $t => $l)
				echo '<li>' .str::link($l, $t). '</li>';
		$breadcrumbs .= '</ul>';

		return $breadcrumbs;
	}

	// Pied de page
	public static function footer($links = array(), $author = 'Le Principe de Stappler', $author_link = 'http://www.stappler.fr/')
	{
		$footer = '&copy;Copyright ' .date('Y');
		if(config::get('sitename')) $footer .= ' - ' .config::get('sitename');
		if(SQL and db::is_table('cerberus_structure')) $footer .= ' - ' .str::slink('sitemap', l::get('menu-sitemap'));
		$footer .= ' - Conception : ' .str::link($author_link, $author);
		if(isset(self::$data['contact']['submenu']['legales'])) $footer .= ' - ' .str::slink('contact-legales', l::get('menu-contact-legales'));
		if(isset(self::$data['contact']['submenu']['contact'])) $footer .= ' - ' .str::slink('contact', l::get('menu-contact'));
		if(!empty($links))
		{
			foreach($links as $text => $link)
				$footer .= ' - ' .str::link($link, $text);
		}
		return $footer;
	}

	//////////////////////////////////////////////////////////////////
	///////////////////////// PUBLIC FUNCTIONS ///////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Change the "listed" options for both the main and sub menus
	 *
	 * @param boolean $menuListed    Main menu should be list-based
	 * @param boolean $submenuListed Submenu should be list-based
	 */
	public static function setListed($menuListed = false, $submenuListed = false)
	{
		self::$optionListed    = $menuListed;
		self::$optionListedSub = $submenuListed;
	}

	/**
	 * Change the glue options for both the main and sub menus
	 *
	 * @param string $optionGlue    The glue for the main menu
	 * @param string $optionGlueSub The glue for the submenu
	 */
	public static function setGlue($optionGlue = null, $optionGlueSub = null)
	{
		self::$optionGlue    = $optionGlue;
		self::$optionGlueSub = $optionGlueSub;
	}

	/**
	 * Alter the main tree's data after it has been built
	 *
	 * @param  string $key        The key to alter, can be page or page-subpage
	 * @param  string $alterValue The value to put in place of the old one
	 * @param  string $alterKey   The key to change
	 */
	public static function alterTree($key, $alterValue = null, $alterKey = 'link')
	{
		// Recursive mode
		if(is_array($key))
		{
			foreach($key as $k) self::alterTree($k, $alterValue, $alterKey);
			return true;
		}

		// Alter a sub page
		if(str::find('-', $key))
		{
			$key = explode('-', $key);
			self::$data[$key[0]]['submenu'][$key[1]][$alterKey] = $alterValue;
		}

		// Alter a parent page
		else self::$data[$key][$alterKey] = $alterValue;
	}

	/**
	 * Get page data from the tree
	 *
	 * @param  string $key The page to look for, or null for whole data array
	 * @return array       The data obtained
	 */
	public static function get($key = null)
	{
		if(!$key) return self::$data;
		else return a::get(self::$data, $key);
	}

	/**
	 * Get the main menu
	 *
	 * @param  boolean $render Render it as HTML or return it as array
	 * @return mixed           The main navigation
	 */
	public static function getMenu($render = true)
	{
		// Ensure the menu is rendered before returning it
		if($render and !self::$renderNavigation) self::render();

		// Render pure or rendered menu
		return ($render) ? self::$renderNavigation : self::get();
	}

	/**
	 * Get the submenu
	 *
	 * @param  boolean $render Render it as HTML or return it as array
	 * @return mixed           The subnavigation
	 */
	public static function getSubmenu($render = true)
	{
		// Fetch the submeny
		$submenu = a::get(self::$data, self::$page.',submenu');

		// If we just want the array, stop here and return
		if(!$render) return $submenu;

		// Render only if not in admin and it for than one subpages
		if(!self::$renderSubnav) self::render();
		return ($submenu and self::$page != 'admin' and count($submenu) > 1)
			? self::$renderSubnav[self::$page]
			: null;
	}

	/**
	 * Get the current page's index
	 * @return string An index formed by page or page-subpage
	 */
	public static function current()
	{
		return self::$sousPage
			? self::$page. '-' .self::$sousPage
			: self::$page;
	}

	/**
	 * Checks if a page is the current page
	 *
	 * @param  string  $page A page index
	 * @return boolean       Current page or not
	 */
	public static function isCurrent($page)
	{
		$type = explode('-', $page);
		if(sizeof($type) == 1) return $page == self::$page;
		else return $page == self::current();
	}

	/**
	 * Returns a sring to use as CSS class for the page
	 * @return string A CSS class as page page-subpage
	 */
	public static function css()
	{
		return self::$page != self::current()
			? self::$page. ' ' .self::current()
			: self::$page;
	}

	//////////////////////////////////////////////////////////////////
	///////////////////////// NAVIGATION TREES ///////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Reset the current navigation
	 */
	public static function reset()
	{
		self::$page             =
		self::$sousPage         =
		self::$renderNavigation =
		self::$renderSubnav     =
		self::$data             = null;
	}

	/**
	 * Takes a raw flatten view of the navigation and rearrange it
	 *
	 * @param  array $dataRaw A flattened version of the navigation
	 * @return array          A rearranged version of it
	 */
	private static function build($dataRaw)
	{
		// Add system pages to raw data, hidden and in cache by default
		foreach(self::$system as $sys) self::addPage($sys, null, 1, 1);

		// Start building the navigaion tree
		foreach($dataRaw as $key => $values)
		{
			// If no parent found, we're on a one-level navigation
			$simpleTree = empty($values['parent']);

			// MENU ------------------------------------------------ /

			// Get the page index according to depth
			$index = !$simpleTree ? $values['parent'] : $values['page'];

			// Fill in parent page informations
			if(!isset($dataRaw[$index]))
			{
				// Getting the number of children
				$subcount = SQL
					? db::count('cerberus_structure', array('parent' => $index))
					: sizeof(a::get($values, 'submenu', array()));

				// If the parent has no child and an external link, then it's an external link
				if($subcount == 1 and !empty($values['external_link']))
				{
					$link       = $values['external_link'];
					$isExternal = 1;
				}
				else
				{
					$link       = null;
					$isExternal = 0;
				}

				// Getting the link text
				$text = a::get($values, 'text', $index);
				$text = l::get('menu-'.$text, ucfirst($text));

				// Getting text, class and link for the page
				$dataRaw[$index] = array(
					'text'     => $text,
					'class'    => array('menu-'.$index),
					'link'     => $link,

					'hidden'   => $values['hidden'],
					'external' => $isExternal,
					);
			}

			// SUBMENU --------------------------------------------- /

			// If we're on a multiple-levels nav and the parent isn't an external link
			if(!$simpleTree and $isExternal != '1')
			{
				// Creating the subpage index and its link
				$indexSub = $values['parent'].'-'.$values['page'];
				$linkSub  = a::get($values, 'external_link');

				// If we don't have an external link, point to page itself
				if(!$linkSub) $linkSub = $index. '-' .$values['page'];

				// If one of the submenu items is supposed to be shown, force the main menu to be shown
				if($values['hidden'] == 0 and $dataRaw[$index]['hidden'] == 1)
					$dataRaw[$index]['hidden'] = 0;

				// Filling in subpage values
				$dataRaw[$index]['submenu'][$values['page']] = array(
					'text'   => l::get('menu-' .$indexSub, ucfirst($values['page'])),
					'class'  => array('menu-'.$index.'-'.$values['page']),
					'link'   => $linkSub,

					'hidden' => $values['hidden'],
					);

				// Calculating the link for subpages
				if(isset($dataRaw[$index]['submenu']))
					foreach($dataRaw[$index]['submenu'] as $subkey => $subvalue)
						$dataRaw[$index]['submenu'][$subkey]['link'] =
							a::get($subvalue, 'link', $values['parent'].'-'.$values['page']);
			}

			// If no link is yet assigned to the parent page, assign it its first children
			if(!a::get($dataRaw, $index.',link') and $isExternal == 0)
			{
				$submenu = a::get($dataRaw, $index. ',submenu');
				$link = $submenu ? $index.'-'.key($submenu) : $index;
				$dataRaw[$index]['link'] = $link;
			}

			// Once we're done building the structured view, remove the original data
			$dataRaw = a::remove($dataRaw, $key);
		}

		// If we're in local, show the link to the admin page
		if(!LOCAL) $dataRaw['admin']['hidden'] = 1;

		// Cache the navigation tree and return it
		cache::fetch('navigation', $dataRaw);
		return $dataRaw;
	}

	/**
	 * Mark as active the links from the current page
	 */
	private static function active()
	{
		// Find the current page
		if(isset(self::$data[self::$page]))
			self::$data[self::$page]['class'][] = 'active';

		// Find the current subpage
		if(isset(self::$data[self::$page]['submenu'][self::$sousPage]))
			self::$data[self::$page]['submenu'][self::$sousPage]['class'][] = 'active';
	}

	/**
	 * Render the navigation tree as an HTML menu
	 *
	 * @param  string $glue The glue separating the links (ex: |)
	 */
	private static function render()
	{
		// Force each link to be on a line in the code
		self::$optionGlue    .= PHP_EOL;
		self::$optionGlueSub .= PHP_EOL;

		// Check if we haven't already rendered that whole mess
		if(!empty(self::$renderNavigation)) return true;

		// Start reading the navigation tree
		foreach(self::$data as $key => $value)
		{
			// Append formatted link
			$renderedLink = self::renderLink($key, $value);
			if($renderedLink) self::$renderNavigation[] = $renderedLink;

			// Reading the submenu
			if(a::get($value, 'submenu'))
			foreach($value['submenu'] as $subkey => $subvalue)
			{
				// Append formatted link
				$renderedLink = self::renderLink($key.'-'.$subkey, $subvalue);
				if($renderedLink) self::$renderSubnav[$key][] = $renderedLink;
			}
		}

		// Imploding the rendered links with given glues
		if(self::$renderNavigation)
			self::$renderNavigation = implode(
				self::$optionGlue,
				self::$renderNavigation);

		if(isset(self::$renderSubnav[self::$page]))
			self::$renderSubnav[self::$page] = implode(
				self::$optionGlueSub,
				self::$renderSubnav[self::$page]);

		// Wrap the navigations in an <ul> tag if Listed
		if(self::$optionListed    and isset(self::$renderNavigation))
			self::$renderNavigation = '<ul>' .self::$renderNavigation.  '</ul>';
		if(self::$optionListedSub and isset(self::$renderSubnav[self::$page]))
			self::$renderSubnav[self::$page] = '<ul>' .self::$renderSubnav[self::$page].'</ul>';
	}

	//////////////////////////////////////////////////////////////////
	//////////////////////////// HELPERS /////////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Adds a page to the navigation tree based on an array of options/a simple name
	 *
	 * @param mixed $pageData An array of parameters for a page, or just the page name
	 */

	private static function addPage($pageData, $parent = null, $cache = 0, $hidden = 0, $external_link = null)
	{
		// Slugify pagename
		$page = is_array($pageData) ? a::get($pageData, 'page') : $pageData;
		$page = str::slugify($page, '_');

		// If we were given a simple page name or a pagedata array
		if(!is_array($pageData))
		{
			$text = $pageData;

			$pageData = array(
				'page' => $page,
				'text' => $text);
		}

		// Writing main informations
		$return = array(
			'page'          => $page,
			'parent'        => a::get($pageData, 'parent',        $parent),
			'text'          => a::get($pageData, 'text'),
			'cache'         => a::get($pageData, 'cache',         $cache),
			'hidden'        => a::get($pageData, 'hidden',        $hidden),
			'external_link' => a::get($pageData, 'external_link', $external_link));

		// Subpages
		if(a::get($pageData, 'submenu'))
		{
			foreach($pageData['submenu'] as $l)
				$submenu[] = self::addPage($l, $return['page']);
			$return['submenu'] = $submenu;
		}

		return $return;
	}

	/**
	 * Render a given page link as HTML menu element
	 *
	 * @param  string $index A page index
	 * @param  array  $value An array of paremeters
	 *
	 * @return string        An HTML menu element
	 */
	private static function renderLink($index, $value)
	{
		// If the link if not supposed to be shown, pass
		if(a::get($value, 'hidden') == 1) return null;

		// Differences between parent/subpage
		$isParent = !str::find('-', $index);
		$isListed = $isParent ? self::$optionListed : self::$optionListedSub;
		if($isParent)
		{
			$firstSubpage = a::get($value, 'submenu', array());
			$firstSubpage = key($firstSubpage);
			$index = $index.'-'.$firstSubpage;
		}

		$attr = array();

		// Build the class attribute
		$class = a::get($value, 'class');
		if(is_array($class)) $class = implode(' ', $class);
		if($class and !$isListed) $attr['class'] = $class;
		$classList = ($class) ? ' class="' .$class. '"' : null;

		// Build the title attribute
		$title = meta::page($index, 'title');
		if($title) $attr['title'] = $title;

		// Get link text and href
		$link = a::get($value, 'link');
		$text = a::get($value, 'text');
		if(!a::get($value, 'external_link'))
			$link = url::rewrite($link);

		// Format the listed or flat version of the link
		$link = $isListed
			? '<li' .$classList. '>' .str::link($link, $text, $attr). '</li>'
			: str::link($link, $text, $attr);

		return $link;
	}

	/**
	 * Check the pages/ folder for a file
	 *
	 * @param  string &$page     The current page
	 * @param  string &$sousPage The current subpage
	 * @return mixed             A filename or an error
	 */
	private static function extension(&$page, &$sousPage)
	{
		// If no pages/ folder found, create it
		if(!file_exists(self::$folder) and !SUBFOLDER) dir::make(self::$folder);

		// If we have a subpage, try finding the specific file for that subpage
		$pageSpecific = $sousPage ? $page.'-'.$sousPage : 'pages/'.$page;

		// Search for an existing file
		$filename = f::exist(
			self::$folder.$pageSpecific.'.html',
			self::$folder.$pageSpecific.'.php',
			self::$folder.$page.'.html',
			self::$folder.$page.'.php');

		// If we found a filename, return it
		if(isset($filename)) return basename($filename);

		// Otherwise, and if we're not on a navigation-less website (one page), throw a 404
		elseif(sizeof(self::$data) != 1 and $page != self::$homepage)
		{
			$page     = 404;
			$sousPage = null;
		}

		return false;
	}
}
