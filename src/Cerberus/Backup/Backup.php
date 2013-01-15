<?php
/**
 *
 * Backup
 *
 * Does backup of the database, manage them
 * clean them and load them back
 */
namespace Cerberus\Backup;

use \Illuminate\Filesystem\Filesystem;
use \Illuminate\Database\DatabaseManager;

class Backup extends Explainer
{
  /**
   * The Filesystem instance
   * @var Filesystem
   */
  protected $files;

  /**
   * The current connection
   * @var DatabaseManager
   */
  protected $connection;

  /**
   * The Manager instance
   * @var Manager
   */
  private $manager;

  /**
   * Initialize the Backup class
   */
  public function __construct(Filesystem $files, DatabaseManager $database, Manager $manager)
  {
    $this->files      = $files;
    $this->manager    = $manager;
    $this->connection = $database->connection();
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
    $this->files->copy($this->manager->getSqliteDatabase(), $filepath);

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
      $this->files->write($filepath, $this->exportTable($table));
    }

    return $this;
  }
}
