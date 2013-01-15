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
   * Path where the dumps will be saved
   * @var string
   */
  private $storage = null;

  /**
   * The current date
   * @var string
   */
  private $date;

  /**
   * Whether debug messages should be printed or not
   * @var boolean
   */
  public $debug = false;

  /**
   * Initialize the Backup class
   */
  public function __construct(\Illuminate\Container\Container $app)
  {
    $this->app = $app;
    $this->manager = new Manager($app);
    $this->connection = $this->app['db']->connection();
  }

  /**
   * Save the database for the given date
   */
  public function save()
  {
    // Read dumps for current date
    $numberDumps = $this->manager->getNumberOfDumpsAt($this->date);
    if ($numberDumps > 0) return $this;

    // Backup SQLite databases
    switch ($this->connection->getDriverName()) {
      case 'sqlite':
        return $this->backupSqliteDatabase();
      case 'mysql':
        return $this->backupMySqlDatabase();
    }

    return $this;
  }

  /**
   * Clean old saves from the files
   */
  public function cleanup()
  {
    $this->manager->cleanup();

    return $this;
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
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

  /**
   * Parse a dump name and return various informations about it
   *
   * @param  string $dump Path to a dump
   * @return array        An array of informations
   */
  private function parseDump($dump)
  {
    $dumpName = basename($dump);

    // Parse filename
    preg_match('/([a-z_]+)_(\d{4})-(\d{2})-(\d{2})@(\d{2})-(\d{2})-(\d{2}).sql/', $dumpName, $matches);
    $unix = mktime($matches[5], $matches[6], $matches[7], $matches[3], $matches[4], $matches[2]);

    return array(
      'dump'    => $dumpName,
      'content' => $this->app['files']->get($dump),
      'date'    => date('Y-m-d', $unix),
      'hour'    => date('H:i:s', $unix),
      'table'   => $matches[1],
      'unix'    => $unix,
    );
  }
}