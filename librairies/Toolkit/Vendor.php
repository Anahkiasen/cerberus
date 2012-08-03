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
    Cerberus\Toolkit\File,
    Cerberus\Toolkit\Arrays;

class Vendor
{
  // Webfonts ------------------------------------------------------ /

  /**
   * Adds embed code for a Typekit account
   * @param  string $typekit A Typekit ID
   */
  public static function typekit($typekit, $async = false)
  {
    // Asynchronous embed code
    if($async) {
      Dispatch::javascript();
      ?>
      <script type="text/javascript">
        (function() {
          var config = {
            kitId: '<?= $typekit ?>',
            scriptTimeout: 3000
          };
          var h=document.getElementsByTagName("html")[0];h.className+=" wf-loading";var t=setTimeout(function()
            {h.className=h.className.replace(/(\s|^)wf-loading(\s|$)/g," ");h.className+=" wf-inactive"},
            config.scriptTimeout);var tk=document.createElement("script"),d=false;tk.src='//use.typekit.net/'+
          config.kitId+'.js';tk.type="text/javascript";tk.async="true";tk.onload=tk.onreadystatechange=function()
          {var a=this.readyState;if(d||a&&a!="complete"&&a!="loaded")return;d=true;clearTimeout(t);try{
            Typekit.load(config)}catch(b){}};var s=document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(tk,s)
        })();
      </script>
      <?php
      Dispatch::closeJavascript();
      return true;
    }

    // Normal embed code
    Dispatch::inject('http://use.typekit.net/' .$typekit. '.js');
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

    $fonts = 'http://fonts.googleapis.com/css?family=' .$fonts;
    Dispatch::stylesheet($fonts);

    return $fonts;
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

  /**
   * Resize and crop an image using TimThumb
   *
   * @param  string  $image      Path to an image
   * @param  integer $width      The new width
   * @param  integer $height     The new height
   * @param  array   $attributes An array of supplementary attributes
   * @return string              Path to an image
   */
  public static function timthumb($image, $width = null, $height = null, $attributes = array())
  {
    // Account for the bug with Windows
    if(\Request::env() == 'home') $image = 'cooperphoto/'.$image;

    $image = 'timthumb.php?src='.$image;
    if($width)  $image .= '&w='.$width;
    if($height) $image .= '&h='.$height;

    return \HTML::image($image);
  }

  /**
   * Generates a placeholder image
   *
   * @param  integer $width      Image width
   * @param  integer $height     Image height
   * @param  string  $text       Facultative text
   * @param  array   $attributes Supplementary attributes (text, format, background, foreground)
   * @return string              An img tag
   */
  public function placeholder($width, $height = null, $attributes = array())
  {
    // Fetch supplementary attributes
    $supplementary = array('format', 'text', 'bgc', 'tc');
    foreach($supplementary as $s)
      ${$s} = Arrays::get($attributes, $s);

    // Create URL
    $image = 'http://placehold.it/'.$width;
    if($height) $image .= 'x'.$height;
    if($bgc)    $image .= '/'.$bgc;
    if($tc)     $image .= '/'.$tc;
    if($text)   $image .= '&text='.$text;
    if($format) $image .= '.'.$format;

    // Create alt attribute
    $alt = $text ? $text : 'placeholder';

    return \HTML::image($image, $alt);
  }
}
