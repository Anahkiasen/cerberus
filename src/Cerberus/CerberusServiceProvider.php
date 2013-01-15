<?php
namespace Cerberus;

use Illuminate\Support\ServiceProvider;

class CerberusServiceProvider extends ServiceProvider
{
  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {
    $this->app['html'] = $this->app->share(function($app) {
      return new HTML($app['url']);
    });

    $this->app['thumb'] = $this->app->share(function($app) {
      return new Thumb($app['url']);
    });

    $this->backup();
  }

  /**
   * Backup the current database
   */
  public function backup()
  {
    $manager = new Backup\Manager($this->app);
    $backup  = new Backup\Backup($this->app['files'], $this->app['db'], $manager);
    $backup->save();
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
}
