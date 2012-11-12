<?php
use \Laravel\CLI\Command;

class Cerberus_Clear_Task
{
  /**
   * Alias for migrate:rebuild
   */
  public function db()
  {
    return Command::run(array('migrate:rebuild'));
  }

  /**
   * Empties all cache directories
   */
  public function cache()
  {
    // If we provided a list of folders to clear
    $folders = func_get_args();
    $folders = $folders[0];
    if (!$folders) $folders = glob(path('storage').'*/');

    // List of folders in the storage folder
    foreach ($folders as $folder) {

      $folder = basename($folder);
      if ($folder == 'work') continue;

      File::cleandir(path('storage').$folder);
    }

    echo 'The cache was successfully cleared';
  }

  /**
   * Clear database and cache
   */
  public function run()
  {
    $this->db();
    $this->cache();
  }
}