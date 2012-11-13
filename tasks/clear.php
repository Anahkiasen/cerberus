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
    $arguments = func_get_args();
    $folders = array_get($arguments, '0.0', '');
    $folders = explode(',', $folders);
    if (!$folders) $folders = glob(path('storage').'*/');

    // Get pattern
    $pattern = array_get($arguments, '0.1', null);
    $pattern = $pattern ? '*'.$pattern.'*' : '*';
    echo 'Clearing files matching : '.$pattern.PHP_EOL;
    $cleared = 0;

    // List of folders in the storage folder
    foreach ($folders as $folder) {

      // Get folder basename
      $folder = basename($folder);
      if ($folder == 'work') continue;

      // Clean all the folder, or only certain files
      $folder = path('storage').$folder;
      $files = glob($folder.'/'.$pattern);
      if (!$files) continue;

      foreach ($files as $file) {
        if (basename($file) == '.gitignore') continue;
        $cleared++;
        File::delete($file);
      }
    }

    echo 'The cache was successfully cleared'.PHP_EOL;
    echo $cleared. ' files deleted'.PHP_EOL;
  }

  /**
   * Clear database and cache
   */
  public function run()
  {
    $this->db();
    $this->cache(array());
  }
}
