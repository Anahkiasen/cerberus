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

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// PUBLIC API //////////////////////////
  ////////////////////////////////////////////////////////////////////

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
    if(class_exists('Basset')) {
      if(isset(Basset::$routes['basset/scripts.js'])) {
        $scripts .= Basset::show('scripts.js');
      }
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
    if(class_exists('Basset')){
      if(isset(Basset::$routes['basset/styles.css'])) {
        $styles .= Basset::show('styles.css');
      }
    }

    return $styles;
  }

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
    self::inject('jquery');

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
   * Add all the styles from a container to the main container
   *
   * @param  string $package The container's name
   */
  public static function container($package)
  {
    $container = Asset::container($package);
    $container = $container->assets;

    foreach($container['style'] as $name => $link)
      Asset::bundle('application')->add($name, $link['source']);
    foreach($container['script'] as $name => $link)
      Asset::bundle('application')->add($name, $link['source']);
  }

  /**
   * Add script/styles to the page
   *
   * @param  string $link An alias/path
   */
  public static function inject($file, $name = null, $filetype = 'add')
  {
    if(!$name) $name = File::name($file);

    $fullPath = path('public').'vendor/'.$file;

    if (isset(self::$aliases[$file])) {
      call_user_func('Asset::'.$filetype, $file, Arrays::get(self::$aliases, $file));
    }
    elseif (file_exists($fullPath) and is_dir($fullPath)) {
      $glob = glob($fullPath.'/{css,js}/*', GLOB_BRACE);
      foreach ($glob as $file) {
        $file = String::remove(path('public'), $file);
        call_user_func('Asset::'.$filetype, $name, $file);
      }
    }
    else {
      call_user_func('Asset::'.$filetype, $name, $file);
    }
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
}
