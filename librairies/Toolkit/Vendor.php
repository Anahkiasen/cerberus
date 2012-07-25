<?php
/**
 *
 * Vendor
 *
 * Adds shortcuts for loading and managing
 * serveral vendor plugins
 */
namespace Cerberus\Toolkit;

use Cerberus\Core\Dispatch;

class Vendor
{
  // Webfonts ---------------------------------------------------------------- /

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

  // Statistics -------------------------------------------------------------- /

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
}
