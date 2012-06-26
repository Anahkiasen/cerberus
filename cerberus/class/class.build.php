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
	 * List of assets (scripts/styles) already parsed
	 * @var array
	 */
	private static $parsedAssets = array();

	/**
	 * A list of assets to treat
	 * @var array
	 */
	private static $build = array(
		'stylesheet' => array(),
		'script'     => array(),
		'image'      => array(),
		'copy'       => array());

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
		set_time_limit(0);

		if(!class_exists('Init'))
		{
			require 'cerberus/class/core.init.php';
			$init = new Init('paths autoloader config constants');
		}

		// Clean build cache
		self::clean();

		// Calculate build file wanted
		$buildFile = r::get('cerberus_build', 'build');
		if(!$buildFile) $buildFile = 'build';
		$buildFile = PATH_CORE.$buildFile.'.json';

		// Attempt at reading a build file
		if(file_exists($buildFile))
		{
			$buildFile = f::read($buildFile, 'json');

			// Getting given paramaters
			$page            = a::get($buildFile, 'page');
			$folder          = a::get($buildFile, 'folder',       self::$folder);
			$additionalFiles = a::get($buildFile, 'addFiles',     self::$additionalFiles);
			$protectedFiles  = a::get($buildFile, 'protectFiles', self::$protectedFiles);
			self::$subfolders = a::get($buildFile, 'subfolders',   self::$subfolders);

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

		// In case we want to build everything
		if(in_array('*', $pages))
		{
			// If not set yet, turn off SQL
			if(!defined('SQL')) define('SQL', false);

			// Create navigation tree
			new navigation();

			// Fetch main pages
			$navigation = null;
			foreach(navigation::get() as $page => $pageData)
			{
				$navigation[] = $page;

				$subPages = a::get($pageData, 'submenu');
				if($subPages)
				{
					$subPages = array_keys($subPages);
					foreach($subPages as $subPage)
						$navigation[] = $page.'-'.$subPage;
				}
			}

			// Replace old pages array with the new
			$pages = $navigation;
		}

		// Add index page to the list of pages to build
		array_unshift($pages, f::name(self::$page, true));

		foreach($pages as $_page)
		{
			// Reset navigation state
			navigation::reset();

			// Set GET variables
			$_GET = array();
			if($_page)
			{
				$get        = explode('-', $_page);
				$getPageSub = a::get($get, 1);
				$getPage    = a::get($get, 0);

				if($getPage) $_GET['page'] = $getPage;
				if($getPageSub) $_GET['pageSub'] = $getPageSub;
			}

			// Crawl the pages
			content::start();
				include self::$page;
			$content = content::end(true);

			// Get images
			self::readImages($content);

			// Rename any links found
			if($_page) self::$moved[url::rewrite($_page)] = $_page.'.html';

			// List assets
			self::listAssets();

			// Write page
			f::write(self::$folder.$_page.'.html', $content);
		}

		// Fetch page and assets
		self::fetch();

		// Correct paths
		self::correctPaths();
	}

	//////////////////////////////////////////////////////////////////
	///////////////////////// CORE FUNCTIONS /////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * List the following assets from a page and add them to the assets to fetch
	 */
	private function listAssets()
	{
		// Merge all assets found
		$assets = array_merge(dispatch::currentCSS(), dispatch::currentJS(), self::$additionalFiles);
		foreach($assets as $asset)
		{
			if(str::find(array('http://', 'https://'), $asset)) continue;

			if(!file_exists(url::strip_query($asset))) continue;

			// Filename
			$filename = f::filename($asset);

			// Filetype
			$type = f::type($asset);
			if(in_array($filename, self::$protectedFiles) or self::isMinified($filename))
				$type = 'copy';

			// If we already logged that file
			if(isset(self::$build[$type]) and in_array($asset, self::$build[$type])) continue;

			// If the image was resized on the go with TimThumb, resize it for good
			if(str::find('timthumb.php', $asset)) $type = 'image';

			// Log the file in the build array
			if(in_array($type, array('stylesheet', 'script', 'image', 'fonts'))) self::$build[$type][] = $asset;
			else self::$build['copy'][] = $asset;
		}
	}

	/**
	 * List all images on a given page
	 *
	 * @param  buffer $buffer An output buffer of a page
	 */
	private function readImages($buffer)
	{
		// Read images from HTML
		$document = new DOMDocument();
		if($buffer)
		{
		    libxml_use_internal_errors(true);
		    $document->loadHTML($buffer);
		    libxml_clear_errors();
		}

		// List images, add their path to the files
		$tags = $document->getElementsByTagName('img');
		foreach($tags as $tag)
		{
			$src = $tag->getAttribute('src');
			if(!in_array($src, self::$additionalFiles))
				self::$additionalFiles[] = $src;
		}

		// Get favicon
		$favicon = $document->getElementsByTagName('link');
		foreach($favicon as $f)
		{
			$rel = $f->getAttribute('rel');
			if($rel and ($rel == 'shortcut icon'))
				self::$additionalFiles[] = $f->getAttribute('href');
		}

		// Read images from CSS
		$css = dispatch::currentCSS();
		foreach($css as $filepath)
		{
			if(in_array($filepath, self::$parsedAssets)) continue;

			$file = f::read($filepath);
			$css = preg_match_all("#url\(['\"]?([^\)]+)['\"']?\)#", $file, $matches);
			$folder = dirname($filepath);

			if(!empty($matches))
			{
				foreach($matches[1] as $match)
				{
					// Get filepath and treat it
					$match = str::remove(array("'", '"'), $match);
					$match = url::strip_query($match);

					// Get real image path
					$pattern = '/\w+\/\.\.\//';
					$match = $folder.'/'.$match;
					while(preg_match($pattern, $match))
					    $match = preg_replace($pattern, '', $match);

					// Add image to path
					if(!in_array($match, self::$additionalFiles))
						self::$additionalFiles[] = $match;
				}
			}

			self::$parsedAssets[] = $filepath;
		}
	}

	/**
	 * Fetch the gathered assets and page and put them in the build folder
	 */
	public function fetch()
	{
		foreach(self::$build as $type => $files)
			foreach($files as $file)
			{
				$concatenatedName = null;
				$subFolder        = self::$subfolders ? f::type($file).'/' : null;
				$folder           = self::$folder.$subFolder;

				// Make sur the destination folder exists
				dir::make($folder);

				// Minify or copy
				switch($type)
				{
					// CSS
					case 'stylesheet':
						$concatenatedName = $folder.self::minifyName('styles.css');
						self::minify($files, $concatenatedName);
						break;

					// Javascript
					case 'script':
						$concatenatedName = $folder.self::minifyName('scripts.js');
						self::minify($files, $concatenatedName);
						break;

					case 'image':
						if(str::find('timthumb.php', $file))
						{
							$timthumb = $file;
							$file = self::unTimthumb($file);
							self::$moved[$timthumb] = $subFolder.$file;
						}
						else self::$moved[$file] = $subFolder.f::filename($file);

						// Compress image and save it
						$newImage = $folder.f::filename($file);
						if(!file_exists($newImage))
							new Resize($file, $newImage, null, null, null, 70);

						break;

					case 'fonts':
						$newPath = self::$folder.f::filename($file);
						self::$moved[$file] = $subFolder.$newPath;
						copy($file, $newPath);
						break;

					// Other
					default:
						if(!self::isMinified($file))
						{
							$concatenatedName = $folder.self::minifyName($file);
							self::minify(array($file), $concatenatedName);
						}

						else copy($file, $folder.f::filename($file));
						break;
				}

				// Calculate final destination and name
				if($concatenatedName) $newPath = $concatenatedName;
				elseif(str::find('timthumb.php', $file)) $newPath = self::$moved[$file];
				else $newPath = $folder.f::filename($file);

				// Save new path
				self::$moved[$file] = $subFolder.f::filename($newPath);
			}
	}

	/**
	 * Corrects the paths to assets in the different files
	 */
	public function correctPaths()
	{
		$files = glob(self::$folder. '*.{html,js,css}', GLOB_BRACE);

		// Correct assets paths in the HTML
		foreach($files as $file)
		{
			$content = f::read($file);
			foreach(self::$moved as $old => $new)
			{
				$content = str_replace('/TrainPignes/'.$old, $new, $content);
				$content = str_replace($old, $new, $content);
			}

			// Write file
			f::write($file, $content);
		}
	}

	//////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////
	//////////////////////////////////////////////////////////////////

	public function copy($old, $new)
	{
		if(true == true)
			return true;
	}

	public function unTimthumb($url)
	{
		$url = parse_url($url);
		$query = a::get($url, 'query');
		if($query)
		{
			// Get TimThumb parameters
			$query = explode('&', $query);
			foreach($query as $k => $v)
			{
				$v = explode('=', $v);
				if(sizeof($v) == 2) $queryArgs[a::get($v, 0)] = a::get($v, 1);
				else $queryArgs[] = a::get($v, 0);
			}

			// Get new path
			$src = $queryArgs['src'];
			$resized = PATH_CACHE.f::filename($src);

			// Create image
			new Resize($src, $resized, a::get($queryArgs, 'w'), a::get($queryArgs, 'h'), null, a::get($queryArgs, 'q'));

			return $resized;
		}
	}

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
		if(!$files) return false;

		$serveOptions['minifiers']['application/x-javascript'] = array('Minify_JS_ClosureCompiler', 'minify');
		$minify = Minify::combine($files, $serveOptions);
		f::write($outputFile, $minify);
	}
}
