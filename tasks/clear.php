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
    // List of folders in the storage folder
    $folders = glob(path('storage').'*/');
    foreach ($folders as $folder) {
      if (basename($folder) == 'work') continue;
      File::cleandir($folder);
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