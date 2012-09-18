<?php
namespace Cerberus\Core;

use \Asset,
    \Basset,
    Cerberus\Toolkit\File,
    Cerberus\Toolkit\Arrays,
    Cerberus\Toolkit\Buffer,
    Cerberus\Toolkit\String;

class Dispatch
{
  /**
   * A list of aliases for different scripts
   * @var array
   */
  private static $aliases = array(

    // jQuery
    'jquery'      => 'https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js',
    'jqueryui'    => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js',

    // SWFObject
    'swfobject'   => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',

    // Plugins jQuery
    'nivoslider'  => array('nivo.slider', 'nivo-slider'),
    'uitotop'     => 'ui.totop');

  /**
   * A list of bits of Javascript
   * @var array
   */
  private static $javascript = array();

  /**
   * A list of Basset containers to display
   * @var array
   */
  private static $basset = array(
    'css' => array('styles'),
    'js' => array('scripts'));

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// PUBLIC API //////////////////////////
  ////////////////////////////////////////////////////////////////////

  // Show scripts and styles --------------------------------------- /

  /**
   * Render the page's scripts
   *
   * @return string HTML markup
   */
  public static function scripts()
  {
    // Fetch Laravel scripts
    $scripts  = Asset::scripts();

    // Fetch Basset scripts
    if (class_exists('Basset')) {
      foreach(self::$basset['js'] as $script)
        $scripts .= self::fetchBasset($script, 'js');
    }

    $scripts .= '<script>'.PHP_EOL.implode(PHP_EOL, self::$javascript).PHP_EOL.'</script>';

    return $scripts;
  }

  /**
   * Render the page's styles
   *
   * @return string HTML markup
   */
  public static function styles()
  {
    // Fetch Laravel styles
    $styles  = Asset::styles();

    // Fetch Basset styles
    if (class_exists('Basset')) {
      foreach(self::$basset['css'] as $style)
        $styles .= self::fetchBasset($style, 'css');
    }

    return $styles;
  }

  // Add scripts or styles ----------------------------------------- /

  /**
   * Add a basic jQuery plugin
   *
   * @param  string $plugin   The plugin name
   * @param  string $selector The selector to apply the plugin to (can be null)
   * @param  array  $params   An array of parameters to pass to the plugin
   */
  public static function plugin($plugin, $selector = null, $params = null)
  {
    // Make sure jQuery is added
    if(!self::issetScript('jquery')) self::inject('jquery');

    // Getting parameters
    $string = $plugin. '(' .json_encode($params). ')';

    // Getting selector
    if ($selector !== null) {
      $selector = empty($selector) ? '$' : "$('" .addslashes($selector). "')";
      $string = $selector.'.'.$string;
    }

    // Adding the JS line
    self::javascript($string.';');
  }

  /**
   * Adds raw Javascript to the page
   *
   * @param  string  $javascript Javascript code
   * @return boolean $after      Place code after files calls or not
   */
  public static function javascript($javascript = null, $after = true)
  {
    if(!$javascript) return Buffer::start();

    // Clean up the script from any wrapping tag
    $javascript = preg_replace('#(<script( type="text/javascript")?>|</script>)#', null, $javascript);
    $javascript = trim($javascript);

    self::$javascript[] = $javascript;
  }

  /**
   * Closes an opened Javascript block
   *
   * @return string The gathered Javascript code
   */
  public static function closeJavascript()
  {
    $javascript = Buffer::get();

    return self::javascript($javascript);
  }

  /**
   * Add script/styles to the page
   *
   * @param  string $link An alias/path
   */
  public static function inject($file, $name = null, $filetype = 'add')
  {
    if(!$name) $name = File::name($file);

    $fullPath = path('public').'components/'.$file;

    if (isset(self::$aliases[$file])) {
      Asset::$filetype($file, Arrays::get(self::$aliases, $file));
    } elseif (file_exists($fullPath) and is_dir($fullPath)) {
      $glob = glob($fullPath.'/{css,js}/*', GLOB_BRACE);
      foreach ($glob as $file) {
        $file = String::remove(path('public'), $file);
        Asset::$filetype($name, $file);
      }
    } else {
      Asset::$filetype($name, $file);
    }
  }

  /**
   * Add a Basset container to the list of containers to display
   *
   * @param  string $name The Basset container name
   * @param  string $type The extension to show (css/js)
   */
  public static function injectBasset($name, $type = null)
  {
    // If no type is specified, try to gather type from extension
    if (!$type) {
      $type = File::extension($name);
      $name = String::remove('.'.$type, $name);
    }

    // If we don't have a correct type, forget it
    if(!in_array($type, array('css', 'js'))) return false;

    // Add container to the list
    self::$basset[$type][] = $name;

    return true;
  }

  // Shortcuts
  public static function __callStatic($method, $parameters)
  {
    switch ($method) {
      case 'script':
      case 'stylesheet':
        if($method == 'stylesheet') $method = 'style';
        $parameters[] = $method;

        return call_user_func_array('self::inject', $parameters);
        break;
    }
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Check if a script already exists within Asset or Basset
   *
   * @param  string  $script The script's name
   * @return boolean         Exists or not
   */
  private static function issetScript($script)
  {
    $asset  = Asset::container('default')->assets;
    $asset  = Arrays::get($asset, 'script');
    $basset = Basset\Container::$shared;

    return isset($basset[$script]) or isset($asset[$script]);
  }

  /**
   * Check if an asset of a given type exists in Basset
   *
   * @param  string $asset The asset's name
   * @param  string $type  The asset's extension
   * @return string        Tags to include the asset, or null
   */
  private static function fetchBasset($asset, $type)
  {
    $asset .= '.'.$type;
    $asset = isset(Basset::$routes['basset/'.$asset])
      ? Basset::show($asset)
      : null;

    return $asset;
  }
}
