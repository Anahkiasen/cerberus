<?php
/**
 * Compile and render a suite of PHP pages as basic HTML pages
 * Concatenate and minify JS/CSS
 *
 * Export for production without any of the core's files, into a subfolder
 */
namespace Cerberus\Modules;

use Cerberus\Core\Dispatch,
  Cerberus\Core\Init,
  Cerberus\Core\Navigation,
  Cerberus\Toolkit\Arrays  as a,
  Cerberus\Toolkit\Content,
  Cerberus\Toolkit\Directory,
  Cerberus\Toolkit\File,
  Cerberus\Toolkit\Request as r,
  Cerberus\Toolkit\String,
  Cerberus\Toolkit\Url;

class Build
{
  // Editable parameters ----------------------------------------- /

  /**
   * The folder in which the files will be exported
   * @var string
   */
  private static $folder          = 'build';

  /**
   * Name of the GET variable to check for
   * @var string
   */
  private static $get             = 'cerberus_build';

  /**
   * Whether assets should be separated in subfolders (css, js, etc)
   * @var boolean
   */
  private static $subfolders      = false;

  /**
   * The current page being built
   * @var string
   */
  private static $page            = null;

  /**
   * The pages that will be built
   * @var array
   */
  private static $getPages        = array();

  /**
   * An array of additional files to add to the build
   * @var array
   */
  private static $additionalFiles = array();

  /**
   * An array of files that must *NOT* be concatenated
   * @var array
   */
  private static $protectedFiles  = array();

  // Private parameters ------------------------------------------ /

  /**
   * List of assets (scripts/styles) already parsed
   * @var array
   */
  private static $parsedAssets    = array();

  /**
   * A list of assets to treat
   * @var array
   */
  private static $build           = array(
    'stylesheet' => array(),
    'script'     => array(),
    'image'      => array(),
    'copy'       => array());

  /**
   * An array referencing each asset's old and new path
   * @var array
   */
  private static $moved           = array();

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
      $init = new Init('paths autoloader config constants');

    // Clean build cache
    self::clean();

    // Calculate build file wanted
    $buildGet   = r::get(self::$get);
    if(empty($buildGet)) $buildGet = 'build';
    $buildJson  = PATH_CORE.$buildGet.'.json';
    $buildIndex = $buildGet.'.php';

    // Attempt at reading a build file
    if(file_exists($buildJson)) $buildFile = File::read($buildJson, 'json');
    elseif(file_exists($buildIndex)) $buildFile = array('page' => $buildIndex);

    // If we found a build config file or a page by that name
    if(!isset($buildFile)) $buildFile = array();

    // Getting given paramaters
    $page             = a::get($buildFile, 'page',        'index.php');
    $folder           = a::get($buildFile, 'folder',       self::$folder);
    $additionalFiles  = a::get($buildFile, 'addFiles',     self::$additionalFiles);
    $protectedFiles   = a::get($buildFile, 'protectFiles', self::$protectedFiles);
    self::$subfolders = a::get($buildFile, 'subfolders',   self::$subfolders);

    // If we don't have any page to load, cancel
    if(!$page) return true;

    if($page)            self::setPage($page);
    if($folder)          self::setFolder($folder);
    if($additionalFiles) self::addFiles($additionalFiles);
    if($protectedFiles)  self::protectFiles($protectedFiles);

    self::includeMinify();

    // Build
    self::$getPages = a::get($buildFile, 'getPages', '*');
    self::getPages(self::$getPages);
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
    self::$getPages = self::detectArguments(func_get_args());

    // In case we want to build everything
    if (in_array('*', self::$getPages)) {
      // If not set yet, turn off SQL
      if(!defined('SQL')) define('SQL', false);

      // Create navigation tree
      new navigation();

      // Fetch main pages
      $navigation = null;
      foreach (navigation::get() as $page => $pageData) {
        $navigation[] = $page;

        $subPages = a::get($pageData, 'submenu');
        if ($subPages) {
          $subPages = array_keys($subPages);
          foreach($subPages as $subPage)
            $navigation[] = $page.'-'.$subPage;
        }
      }

      // Replace old pages array with the new
      self::$getPages = $navigation;
    }

    // Add index page to the list of pages to build
    array_unshift(self::$getPages, File::name(self::$page, true));

    // Clean the array of pages to build
    self::$getPages = a::clean(self::$getPages);

    foreach (self::$getPages as $_page) {
      // Reset navigation state
      navigation::reset();

      // Set GET variables
      $_GET = array();
      if ($_page) {
        $get    = explode('-', $_page);
        $getPageSub = a::get($get, 1);
        $getPage  = a::get($get, 0);

        if($getPage)  $_GET['page']  = $getPage;
        if($getPageSub) $_GET['pageSub'] = $getPageSub;
      }

      // Crawl the pages
      content::start();
        include self::$page;
      $content = content::end(true);

      // Get images
      self::readImages($content);

      // Crawl links
      self::readLinks($content);

      // Rename any links found
      if($_page) self::$moved[url::rewrite($_page)] = $_page.'.html';

      // List assets
      self::listAssets();

      // Write page
      File::write(self::$folder.$_page.'.html', $content);
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
    foreach ($assets as $asset) {
      if(String::find(array('http://', 'https://'), $asset)) continue;

      if(!file_exists(url::strip_query($asset))) continue;

      // Filename
      $filename = File::filename($asset);

      // Filetype
      $type = File::type($asset);
      if(in_array($filename, self::$protectedFiles) or self::isMinified($filename))
        $type = 'copy';

      // If we already logged that file
      if(isset(self::$build[$type]) and in_array($asset, self::$build[$type])) continue;

      // Inline @import in CSS files (only http for now)
      if ($type == 'stylesheet') {
        $content = File::read($asset);
        preg_match_all('#@import url\((\'|")?(.+)(\'|")?\);#', $content, $matches);
        foreach ($matches[2] as $key => $match) {
          $import = file_get_contents($match);
          if($import) $content = str_replace($matches[0][$key], $import, $content);
        }
        File::write($asset, $content);
      }

      // If the image was resized on the go with TimThumb, resize it for good
      if(String::find('timthumb.php', $asset)) $type = 'image';

      // Log the file in the build array
      if(in_array($type, array('stylesheet', 'script', 'image', 'fonts'))) self::$build[$type][] = $asset;
      else self::$build['copy'][] = $asset;
    }
  }

  /**
   * List all images on a given page
   *
   * @param buffer $buffer An output buffer of a page
   */
  private function readImages($buffer)
  {
    // Read images from HTML
    $document = self::getHTML($buffer);

    // List images, add their path to the files
    $tags = $document->getElementsByTagName('img');
    foreach ($tags as $tag) {
      $src = $tag->getAttribute('src');
      if(!in_array($src, self::$additionalFiles))
        self::$additionalFiles[] = $src;
    }

    // Get favicon
    $favicon = $document->getElementsByTagName('link');
    foreach ($favicon as $f) {
      $rel = $f->getAttribute('rel');
      if($rel and ($rel == 'shortcut icon'))
        self::$additionalFiles[] = $f->getAttribute('href');
    }

    // Read images from CSS
    $css = dispatch::currentCSS();
    foreach ($css as $filepath) {
      if(in_array($filepath, self::$parsedAssets)) continue;

      $file = File::read($filepath);
      $css = preg_match_all("#url\(['\"]?([^\)]+)['\"']?\)#", $file, $matches);
      $folder = dirname($filepath);

      if (!empty($matches)) {
        foreach ($matches[1] as $match) {
          // Get filepath and treat it
          $match = String::remove(array("'", '"'), $match);
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

  public function readLinks($buffer)
  {
    // Read links from HTML
    $document = self::getHTML($buffer);

    // List links
    $tags = $document->getElementsByTagName('a');
    foreach ($tags as $tag) {
      $href = $tag->getAttribute('href');

      // If not an external link
      if(String::find('http', $href)) continue;
      continue;
    }
  }

  /**
   * Fetch the gathered assets and page and put them in the build folder
   */
  public function fetch()
  {
    foreach (self::$build as $type => $files)
      foreach ($files as $file) {
        $concatenatedName = null;
        $subFolder    = self::$subfolders ? File::type($file, $type).'/' : null;
        $folder       = self::$folder.$subFolder;

        // Make sur the destination folder exists
        Directory::make($folder);

        // Minify or copy
        switch ($type) {
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

            // Create resized versions of TimThumb pics
            if (String::find('timthumb.php', $file)) {
              $concatenatedName = self::unTimthumb($file, $folder);
              continue;
            }

            // Write new path
            self::$moved[$file] = $subFolder.File::filename($file);

            // Compress image and save it
            $newImage = $folder.File::filename($file);
            if (!file_exists($newImage)) {
              // If we can compress it
              if(File::extension($file) != 'png') new Resize($file, $newImage, null, null, null, 70);
              else copy($file, $newImage);
            }

            break;

          case 'fonts':
            $newPath = self::$folder.File::filename($file);
            self::$moved[$file] = $subFolder.$newPath;
            copy($file, $newPath);
            break;

          // Other
          default:
            if (!self::isMinified($file)) {
              $concatenatedName = $folder.self::minifyName($file);
              self::minify(array($file), $concatenatedName);
            } else copy($file, $folder.File::filename($file));
            break;
        }

        // Calculate final destination and name
        if($concatenatedName) $newPath = $concatenatedName;
        else $newPath = $folder.File::filename($file);

        // Save new path
        self::$moved[$file] = $subFolder.File::filename($newPath);
      }
  }

  /**
   * Corrects the paths to assets in the different files
   */
  public function correctPaths()
  {
    // Get current folder cerberus/classes/Modules/ for removal in CSS
    $files  = glob(self::$folder. '{/*/*,*}.{html,js,css}', GLOB_BRACE);
    $folder = Directory::nth(__DIR__, 3);

    // Correct assets paths in the HTML, JS and CSS files
    foreach ($files as $file) {
      $content = File::read($file);
      foreach (self::$moved as $old => $new) {

        // Cache-bust assets
        $buster = (File::extension($new) != 'html') ? '?'.time() : null;

        // Replace paths
        $content = str_replace('/'.$folder.'/'.$old, $new.$buster, $content);
        $content = str_replace($old, $new.$buster, $content);
      }

      // Write file
      File::write($file, $content);
    }
  }

  //////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS ///////////////////////////
  //////////////////////////////////////////////////////////////////

  public function getHTML($buffer)
  {
    // Read images from HTML
    $document = new \DOMDocument();
    if ($buffer) {
      libxml_use_internal_errors(true);
      $document->loadHTML($buffer);
      libxml_clear_errors();
    }

    return $document;
  }

  public function copy($old, $new)
  {
    if(true == true)

      return true;
  }

  public function unTimthumb($url, $to)
  {
    $url = parse_url($url);
    $query = a::get($url, 'query');
    if ($query) {
      // Get TimThumb parameters
      $query = explode('&', $query);
      foreach ($query as $k => $v) {
        $v = explode('=', $v);
        if(sizeof($v) == 2) $queryArgs[a::get($v, 0)] = a::get($v, 1);
        else $queryArgs[] = a::get($v, 0);
      }

      // Get new path
      $src = $queryArgs['src'];

      // Create image
      $to = $to.File::filename($src);
      new Resize($src, $to, a::get($queryArgs, 'w'), a::get($queryArgs, 'h'), null, a::get($queryArgs, 'q'));

      return $to;
    }
  }

  /**
   * Add a .min. tag to a filename
   * @param  string $filename A filename
   * @return string A modified filename
   */
  private function minifyName($filename)
  {
    return File::name($filename, true).'.min.'.File::extension($filename);
  }

  /**
   * Check if a file is already minified or not
   * @param  string  $filename A filename
   * @return boolean Whether .min or -min was found in the filename
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
    Directory::remove(self::$folder);
    Directory::make(self::$folder);
  }

  /**
   * Detect by what way we were given the array in the arguments
   * @param  array $arguments The arguments of a function
   * @return array An array
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
   * @param array  $files    Files to treat
   * @param string $outputFile The final file to output
   */
  private function minify($files, $outputFile)
  {
    if(!$files) return false;

    $serveOptions['minifiers']['application/x-javascript'] = array('Minify_JS_ClosureCompiler', 'minify');
    $minify = \Minify::combine($files, $serveOptions);
    File::write($outputFile, $minify);
  }
}
