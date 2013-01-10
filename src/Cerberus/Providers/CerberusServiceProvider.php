<?php
namespace Cerberus\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Response;

use Cerberus\HTML;
use Cerberus\Thumb;

class CerberusServiceProvider extends ServiceProvider
{
  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {
    $this->registerGlow();

    $this->app['html'] = $this->app->share(function($app) {
      return new HTML($app['url']);
    });

    $this->app['thumb'] = $this->app->share(function($app) {
      return new Thumb($app['url']);
    });
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides()
  {
    return array('html');
  }

  /**
   * Register the Glow javascript helper
   */
  private function registerGlow()
  {
    $app = $this->app;

    $this->app['router']->get('glow.js', function() use ($app) {

      // Get Illuminate's glow
      $js = $this->app['files']->get('packages/anahkiasen/cerberus/js/glow.js');
      $js = str_replace('%BASE%', $this->app['url']->getRequest()->root(), $js);
      $js = str_replace('%ASSET%', $this->app['url']->asset(''), $js);

      // Set correct header
      $headers['Content-Type'] = 'application/javascript; charset=utf-8';

      return new Response($js, 200, $headers);
    });

  }
}