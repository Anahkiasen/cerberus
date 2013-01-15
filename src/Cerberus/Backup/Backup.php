<?php
/**
 *
 * Backup
 *
 * Does backup of the database, manage them
 * clean them and load them back
 */
namespace Cerberus\Backup;

use \Config;
use \Lang;
use \Laravel\Database as DB;
use \Underscore\Types\Arrays;

class Backup extends Explainer
{
  /**
   * The application instance
   * @var Container
   */
  protected $app;

  /**
   * The Manager instance
   * @var Manager
   */
  private $mananger;

  /**
   * Initialize the Backup class
   */
  public function __construct(\Illuminate\Container\Container $app, Manager $manager)
  {
    $this->app = $app;
    $this->manager = $manager;
    $this->connection = $this->app['db']->connection();
  }

  /**
   * Save the database for the given date
   */
  public function save()
  {
    // Read dumps for current date
    $numberDumps = $this->manager->getNumberOfDumpsAt();
    if ($numberDumps > 0) return $this;

    // Backup databases
    switch ($this->connection->getDriverName()) {
      case 'sqlite':
        return $this->backupSqliteDatabase();
      case 'mysql':
        return $this->backupMySqlDatabase();
    }

    // Clean old database
    $this->manager->cleanup();

    return $this;
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// BACKUP ROUTINES ////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Backup a SQLite database
   *
   * @return Backup
   */
  private function backupSqliteDatabase()
  {
    $filepath = $this->manager->getFilenameForToday('production').'ite';
    $this->app['files']->copy($this->app->path.'/database/production.sqlite', $filepath);

    return $this;
  }

  /**
   * Backup a MySQL database
   *
   * @return Backup
   */
  private function backupMySqlDatabase()
  {
    foreach ($this->getTables() as $table) {
      $filepath = $this->getFolderForToday($table);
      $this->app['files']->write($filepath, $this->exportTable($table));
    }

    return $this;
  }
}