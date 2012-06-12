<?php
/**
 * Compile and render a suite of PHP pages as basic HTML pages
 * Concatenate and minify JS/CSS
 *
 * Export for production without any of the core's files, into a subfolder
 */
class Build
{
	// Editable parameters ----------------------------------------- /

	/**
	 * The folder in which the files will be exported
	 * @var string
	 */
	private static $folder = 'build';

	/**
	 * Whether assets should be separated in subfolders (css, js, etc)
	 * @var boolean
	 */
	private static $subfolders = false;

	/**
	 * The current page being built
	 * @var string
	 */
	private static $page = null;

	/**
	 * An array of additional files to add to the build
	 * @var array
	 */
	private static $additionalFiles = array();

	/**
	 * An array of files that must *NOT* be concatenated
	 * @var array
	 */
	private static $protectedFiles = array();

	// Private parameters ------------------------------------------ /

	/**
	 * The current page's HTML content
	 * @var string
	 */
	private static $pageContent = null;

	/**
	 * A list of assets to treat
	 * @var array
	 */
	private static $build = array(
		'css'  => array(),
		'js'   => array(),
		'copy' => array());

	/**
	 * An array referencing each asset's old and new path
	 * @var array
	 */
	private static $moved = array();

	//////////////////////////////////////////////////////////////////
	////////////////////////// PUBLIC API ////////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Start the building of the website
	 */
	public function __construct()
	{
		if(!class_exists('Init'))
		{
			require 'cerberus/class/core.init.php';
			$init = new Init('constants autoloader config');
		}

		// Clean build cache
		self::clean();

		// Attempt at reading a build file
		$buildFile = PATH_CORE.'build.json';
		if(file_exists($buildFile))
		{
			$buildFile = f::read($buildFile, 'json');

			// Getting given paramaters
			$page            = a::get($buildFile, 'page');
			$folder          = a::get($buildFile, 'folder',       self::$folder);
			$additionalFiles = a::get($buildFile, 'addFiles',     self::$additionalFiles);
			$protectedFiles  = a::get($buildFile, 'protectFiles', self::$protectedFiles);
			$subfolders      = a::get($buildFile, 'subfolders');

			// If we don't have any page to load, cancel
			if(!$page) return true;

			if($page)            self::setPage($page);
			if($folder)          self::setFolder($folder);
			if($additionalFiles) self::addFiles($additionalFiles);
			if($protectedFiles)  self::protectFiles($protectedFiles);

			self::includeMinify();

			// Build
			$build = a::get($buildFile, 'getPages');
			self::getPages($build);
		}
	}

	/**
	 * Set the main file to be used as base
	 * @param string $page A filename
	 */
	public function setPage($page)
	{
		self::$page = $page;
	}

	/**
	 * Set the folder the website will be built in
	 * @param string $folder A folder name, with or without the trailing /
	 */
	public function setFolder($folder)
	{
		if(substr($folder, 0, -1) != '/') $folder .= '/';
		self::$folder = $folder;
	}

	/**
	 * Add files to be manually added to the build's folder
	 * @param Files to add
	 */
	public function addFiles()
	{
		$files = self::detectArguments(func_get_args());
		self::$additionalFiles = array_merge(self::$additionalFiles, $files);
	}

	/**
	 * Add files to be protected (won't be minified nor concatenated)
	 * @return Files to protect
	 */
	public function protectfiles()
	{
		$files = self::detectArguments(func_get_args());
		self::$protectedFiles = array_merge(self::$protectedFiles, $files);
	}

	/**
	 * Build a given arg_array of pages
	 */
	public function getPages()
	{
		// Setup list of pages to crawl
		$pages = self::detectArguments(func_get_args());
		if(!a::get($pages, 0)) $pages = array(f::name(self::$page, true));

		foreach($pages as $page)
		{
			$_GET = array();
			if($page) $_GET['page'] = $page;

			// Crawl the pages
			content::start();
				include self::$page;
			self::$pageContent[$page] = content::end(true);

			// Rename any links found
			if($page) self::$moved[url::rewrite($page)] = $page.'.html';

			// List assets
			self::listAssets();
		}

		self::fetch();
	}

	//////////////////////////////////////////////////////////////////
	///////////////////////// CORE FUNCTIONS /////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * List the following assets from a page and add them to the assets to fetch
	 */
	private function listAssets()
	{
		// Save assets
		$assets = array_merge(dispatch::currentCSS(), dispatch::currentJS(), self::$additionalFiles);
		foreach($assets as $asset)
		{
			if(!file_exists($asset)) continue;

			// Filename
			$filename = f::filename($asset);

			// Filetype
			$type = f::extension($asset);
			if(in_array($filename, self::$protectedFiles) or self::isMinified($filename))
				$type = 'copy';

			if(in_array($asset, self::$build[$type])) continue;

			switch($type)
			{
				case 'css':
					self::$build['css'][] = $asset;
					break;

				case 'js':
					self::$build['js'][] = $asset;
					break;

				default:
					self::$build['copy'][] = $asset;
					break;
			}
		}
	}

	/**
	 * Fetch the gathered assets and page and put them in the build folder
	 */
	public function fetch()
	{
		foreach(self::$build as $type => $files)
		{
			$concatenatedName = null;

			// Minify or copy
			switch($type)
			{
				// CSS
				case 'css':
					$concatenatedName = self::$folder.self::minifyName('styles.css');
					self::minify($files, $concatenatedName);
					break;

				// Javascript
				case 'js':
					$concatenatedName = self::$folder.self::minifyName('scripts.js');
					self::minify($files, $concatenatedName);
					break;

				// Other
				default:
					foreach($files as $file)
					{
						if(!self::isMinified($file))
						{
							$concatenatedName = self::$folder.self::minifyName($file);
							self::minify(array($file), $concatenatedName);
						}

						else copy($file, self::$folder.f::filename($file));
					}
					break;
			}

			// Recording the new filepaths
			foreach($files as $file)
			{
				if($concatenatedName) $newPath = $concatenatedName;
				else $newPath = self::$folder.f::filename($file);
				self::$moved[$file] = f::filename($newPath);
			}
		}

		foreach(self::$pageContent as $page => $content)
		{
			// Correct assets paths in the HTML
			foreach(self::$moved as $old => $new)
				$content = str_replace($old, $new, $content);

			// Write file
			f::write(self::$folder.$page.'.html', $content);
		}
	}

	//////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Add a .min. tag to a filename
	 * @param  string $filename A filename
	 * @return string           A modified filename
	 */
	private function minifyName($filename)
	{
		return f::name($filename, true).'.min.'.f::extension($filename);
	}

	/**
	 * Check if a file is already minified or not
	 * @param  string  $filename A filename
	 * @return boolean           Whether .min or -min was found in the filename
	 */
	private function isMinified($filename)
	{
		return preg_match('#.+([\.\-](pack|min))\.(css|js)#', $filename);
	}

	/**
	 * Include Minify's required files
	 */
	private function includeMinify()
	{
		// Minify
		include PATH_MAIN.'min/lib/Minify.php';
		include PATH_MAIN.'min/lib/Minify/Controller/Base.php';
		include PATH_MAIN.'min/lib/Minify/Controller/Files.php';
		include PATH_MAIN.'min/lib/Minify/CommentPreserver.php';

		// CSS
		include PATH_MAIN.'min/lib/Minify/CSS.php';
		include PATH_MAIN.'min/lib/Minify/CSS/Compressor.php';
		include PATH_MAIN.'min/lib/Minify/CSS/UriRewriter.php';

		// Javascript
		include PATH_MAIN.'min/lib/JSMin.php';
		include PATH_MAIN.'min/lib/Minify/JS/ClosureCompiler.php';
	}

	/**
	 * Clean the build folder
	 */
	private function clean()
	{
		dir::remove(self::$folder);
		dir::make(self::$folder);
	}

	/**
	 * Detect by what way we were given the array in the arguments
	 * @param  array $arguments The arguments of a function
	 * @return array            An array
	 */
	private function detectArguments($arguments)
	{
		$argument0 = a::get($arguments, 0);
		if(sizeof($arguments) == 1 and is_array($argument0)) return $argument0;
		elseif(sizeof($arguments) == 1 and !is_array($argument0)) return array($argument0);
		else return $arguments;
	}

	/**
	 * Concatenate and minify CSS
	 * @param  array  $files      Files to treat
	 * @param  string $outputFile The final file to output
	 */
	private function minify($files, $outputFile)
	{
		$serveOptions['minifiers']['application/x-javascript'] = array('Minify_JS_ClosureCompiler', 'minify');
		$minify = Minify::combine($files, $serveOptions);
		f::write($outputFile, $minify);
	}
}