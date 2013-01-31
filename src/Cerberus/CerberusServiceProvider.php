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

    $this->app->bind('Symfony\Component\HttpFoundation\Request', 'Illuminate\Http\Request');
    $this->app->singleton('html', 'Cerberus\HTML');
    $this->app->bind('thumb', 'Cerberus\Thumb');

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
    $this->app['command.cerberus.clear'] = $this->app->share(function($app)
    {
      return new Commands\Clear($app['files']);
    });

    $this->commands('command.cerberus.clear');
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
