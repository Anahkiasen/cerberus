<?php
namespace Cerberus\Backup;

use \Underscore\Types\Arrays;

class Manager
{
  public $storage = null;

  /**
   * Startup the backups manager
   *
   * @param Illuminate\Container\Container $app
   */
  public function __construct(\Illuminate\Container\Container $app)
  {
    $this->app = $app;

    // Set backups folder
    $this->storage = $this->app->path.'/storage/database/';
    $this->date = date('Y-m-d');

    // Create folder if it doesn't exist
    if(!file_exists($this->storage)) {
      $this->app['files']->makeDirectory($this->storage);
    }
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// FOLDERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the path to the SQlite Database
   *
   * @return string
   */
  public function getSqliteDatabase()
  {
    return $this->app->path.'/database/production.sqlite';
  }

  /**
   * Get all folders
   */
  public function getAllFolders()
  {
    return glob($this->storage.'*');
  }

  /**
   * Get the date of a backup folder
   *
   * @param string $folder The folder path
   *
   * @return DateTime
   */
  public function getDateFromFolder($folder)
  {
    return new \DateTime(basename($folder));
  }

  /**
   * Get the full path to a date's folder
   *
   * @param  string $date A date in the format YYYY-mm-dd
   * @return string       A folder path
   */
  public function getFolderForDate($date = null)
  {
    return $this->storage.$date.'/';
  }

  /**
   * Get today's dumps folder
   *
   * @return string
   */
  public function getFolderForToday()
  {
    $folder = $this->getFolderForDate($this->date);
    if (!file_exists($folder)) $this->app['files']->makeDirectory($folder);

    return $folder;
  }

  /**
   * Get the number of dumps for a date
   *
   * @param string $date A date in the format YYYY-mm-dd
   * @return integer A number of dumps
   */
  public function getNumberOfDumpsAt($date = null)
  {
    if(!$date) $date = $this->date;
    $dumps = $this->getFolderForDate($date);

    return file_exists($dumps) ? sizeof(glob($dumps.'*')) : 0;
  }

  /**
   * Generate a filename for today
   *
   * @param string $table The table for
   */
  public function getFilenameForToday($table)
  {
    return $this->getFolderForToday().$table.'_'.$this->date.date('@H-m-s').'.sql';
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// MAINTENANCE ///////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Clean old save folders
   *
   * @return Manager
   */
  public function cleanup()
  {
    // Fetch all dumps folders
    $folders = $this->getAllFolders();
    $folders = Arrays::filter($folders, function($folder) {
      return is_dir($folder);
    });

    // Parse the date for each one
    foreach ($folders as $folder) {

      // Get date from folder name
      $date = $this->getDateFromFolder($folder);

      // If dump from last year, remove
      if ($date->format('Y') < date('Y')) {
        $this->app['files']->deleteDirectory($folder);
        continue;
      }

      // If dump from last months, just keep main checkpoints
      if ($date->format('m') < date('m') and !in_array($date->format('d'), array(1, 15))) {
        $this->app['files']->deleteDirectory($folder);
        continue;
      }
    }

    return $this;
  }
}
