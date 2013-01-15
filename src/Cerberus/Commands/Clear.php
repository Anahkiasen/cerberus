<?php
namespace Cerberus\Commands;

use \Underscore\Types\Arrays;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Clear extends Command
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'cerberus:clear';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clears various caches';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct($app)
  {
    parent::__construct();

    $this->app = $app;
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function fire()
  {
    // Clear storage folders
    $folders = $this->getFoldersToClear();
    $this->info('The following folders will be cleared : '. implode(', ', $folders));
    $this->clearFolders($folders);

    // Clear database
    if ($this->option('database')) $this->clearDatabase();
  }

  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return array(
      array('folder', InputArgument::OPTIONAL, 'A particular folder to clear'),
    );
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return array(
      array('database', 'db', InputOption::VALUE_NONE, 'Clears and rebuild the database')
    );
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////////// HELPERS ///////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Clears and rebuild the database
   */
  private function clearDatabase()
  {
    $this->call('migrate:refresh', array('--seed' => null));
  }

  /**
   * Clear the content of various storage folders
   *
   * @param array $folders An array of folders to clear
   */
  private function clearFolders($folders)
  {
    foreach ($folders as $folder) {
      $folder  = $this->app->path.'/storage/'.$folder;
      $files   = $this->app['files']->files($folder);
      $deleted = 0;

      // Get files
      foreach ($files as $file) {
        $deleted++;
        $this->app['files']->delete($file);
      }

      $this->info(basename($folder). ' cleaned (' .$deleted. ' files deleted)');
    }
  }

  /**
   * Get a list of folders to clear
   *
   * @return array
   */
  private function getFoldersToClear()
  {
    $folders = $this->argument('folder');
    $folders = $folders ? explode(',', $folders) : null;

    // If no folders provided, get all folders
    if (!$folders) {
      $storage = $this->app->path.'/storage/*';
      $folders = $this->app['files']->glob($storage);
      $folders = Arrays::each($folders, function($folder) { return basename($folder); });
    }

    return $folders;
  }

}
