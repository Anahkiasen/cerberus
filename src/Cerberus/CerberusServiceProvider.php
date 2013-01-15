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
    $this->package('anahkiasen/cerberus');

    $this->app['html'] = $this->app->share(function($app) {
      return new HTML($app['url']);
    });

    $this->app['thumb'] = $this->app->share(function($app) {
      return new Thumb($app['url']);
    });

    $this->backup();
    $this->registerCommands();
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
   * Register the artisan commands
   */
  public function registerCommands()
  {
    $this->app['command.cerberus.clean'] = $this->app->share(function($app)
    {
      return new Clean($app);
    });

    $this->commands('command.cerberus.clean');
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
