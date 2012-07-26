<?php
/**
 *
 * Vendor
 *
 * Adds shortcuts for loading and managing
 * serveral vendor plugins
 */
namespace Cerberus\Toolkit;

use Cerberus\Core\Dispatch,
    Cerberus\Toolkit\File;

class Vendor
{
  // Webfonts ------------------------------------------------------ /

  /**
   * Adds embed code for a Typekit account
   * @param  string $typekit A Typekit ID
   */
  public static function typekit($typekit)
  {
    Dispatch::script('//use.typekit.net/' .$typekit. '.js');
    Dispatch::javascript('try{Typekit.load();}catch(e){}');
  }

  /**
   * Embbed Google webfonts on the page
   * @param  string Several fonts to include
   */
  public static function googleFonts()
  {
    $fonts = func_get_args();

    // Fetching the fonts, converting to GF syntax
    $fonts = implode('|', $fonts);
    $fonts = str_replace(' ' , '+', $fonts);
    $fonts = str_replace('*' , '100,200,300,400,500,600,700,800,900', $fonts);

    Dispatch::stylesheet('http://fonts.googleapis.com/css?family=' .$fonts);
  }

  // Statistics ---------------------------------------------------- /

  /**
   * Add a Google Analytics account
   * @param  string $analytics A Google Analytics ID
   */
  public static function googleAnalytics($analytics = 'XXXXX-X')
  {
    Dispatch::javascript(
      "var _gaq=_gaq||[];" .
      "_gaq.push(['_setAccount','UA-" .$analytics. "']);" .
      "_gaq.push(['_trackPageview']);" .
      "(function(){var ga=document.createElement('script');" .
      "ga.type='text/javascript';" .
      "ga.async=true;" .
      "ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';" .
      "var s=document.getElementsByTagName('script')[0];" .
      "s.parentNode.insertBefore(ga,s)})();"
      );
  }

  // Assets -------------------------------------------------------- /

  /**
   * Generates a Compass configuration file
   *
   * @param  string  $writePath The folder to write it (defaults to root)
   * @param  array   $config    Configuration options
   * @param  array   $plugins   Additional Rubygems to load
   * @return boolean            Success in creating the file or not
   */
  public static function compass($writePath = null, $configuration = array(), $plugins = array())
  {
    $fileName = 'config.rb';

    // Check if the file already exists
    if(file_exists($writePath.$fileName)) return true;

    // Create an empty file
    $file = null;

    // Default configuration and plugins
    $defaults = array(
      '0'                  => 'Folders',
        'project_path'     => 'public/',
        'images_dir'       => 'img',
        'css_dir'          => 'css',
        'javascripts_dir'  => 'js',
        'fonts_dir'        => 'fonts',

      '1'                  => 'Options',
        'output_style'     => ':expanded',
        'preferred_syntax' => ':sass',
        'line_comments'    => 'false',
        'relative_assets'  => 'true',

      '2'                  => 'Extensions'
    );
    $defaultsPlugins = array(
      'susy',
      'animate',
      'rgbapng',
      'modular-scale',
      'normalize'
    );

    // Merge with given configuration parameters
    $configuration = array_merge($configuration, $defaults);
    $plugins       = array_merge($plugins, $defaultsPlugins);

    // Writing options
    foreach ($configuration as $k => $v)
    {
      // If value is comment
      if (is_numeric($k))
      {
        if(!empty($file)) $file .= PHP_EOL;
        $file .= '# ' . $v . PHP_EOL;
        continue;
      }


      // If value is array
      elseif(is_array($v))
        $v = json_encode($v);

      // If value is neither a boolean nor a symbol
      elseif(
        !($v == 'true' or
          $v == 'false' or
          substr($v, 0, 1) == ':')
      ) $v = '"' . $v . '"';

      // Else, just print value
      $file .= $k . ' = ' . $v . PHP_EOL;
    }

    // Loading extensions
    foreach($plugins as $e)
      $file .= "require '" .$e. "'" . PHP_EOL;

    // Write core configuration file in root
    return File::write($writePath.$fileName, $file);
  }
}
