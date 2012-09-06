<?php
/**
 *
 * Backup
 *
 * Does backup of the database, manage them
 * clean them and load them back
 */

namespace Cerberus\Modules;

use Cerberus\Toolkit\Directory,
    Cerberus\Toolkit\File,
    Laravel\Database as DB;

class Backup
{
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
  public function __construct()
  {
    // Set correct storage path
    $this->storage = path('storage').'sql/';

    // Create folder if it doesn't exist
    if(!file_exists($this->storage)) Directory::make($this->storage);

    // Cache current date
    $this->date = date('Y-m-d');
  }

  /**
   * Save the current database if necessary
   *
   * @return boolean Whether the database was saved or not
   */
  public function save()
  {
    $tables = $this->tables();
    $unsavedTables = array();

    // If we have tables to save
    if ($tables) {

      // Get database name
      $database = DB::connection()->config['database'];
      $this->debug('info', 'Saving database `' .$database. '`');

      // Read dumps for current date
      $dumps = $this->readDumps();
      if (!empty($dumps)) {
        $this->debug(
          'success',
          'A dump for today (' .$this->date. ') already exists (' .sizeof($dumps). ' tables in memory).');

        return true;
      }

      // If no dumps, save tables
      $folder = $this->getFolderForDate();
      foreach ($tables as $table) {
        $filepath = $folder.$table.'_'.$this->date.date('@H-m-s').'.sql';
        list($export, $numberRows) = $this->exportTable($table);

        $write = File::write($filepath, $export);
        if ($write) {
          $this->debug('success', 'Table ' .$table. ' saved successfully (' .$numberRows. ' rows)');
        } else {
          $unsavedTables[] = $table;
          $this->debug('error', 'An error occured saving table `' .$table. '`');
        }
      }

      // Make sure all tables were correctly saved
      if (empty($unsavedTables)) {
        $this->debug('success', 'Database saved successfully');
    } else {
        $this->debug('error', 'The following tables could not be saved: ' .implode(', ', $unsavedTables));
      }
    }
    else {
      $this->debug('info', 'No tables to save');

      return true;
    }
  }

  /**
   * List all the dumps from a folder
   *
   * @param  string $folder A folder path
   * @return array          An array of dumps available
   */
  public function readDumps($folder = null)
  {
    if(!$folder) $folder = $this->getFolderForDate();

    // If the folder doesn't exist, return empty array
    if(!file_exists($folder)) return array();

    // Fetch all dumps for that date
    $dumps = glob($folder.'*.sql');

    return $dumps;
  }

  /**
   * Export a table in SQL format
   * @param  string $table The table name
   * @return array         The SQL dump ; Number of rows in table
   */
  public function exportTable($table)
  {
    // Add premptive DROP TABLE
    $dump = null;
    $dump .= 'DROP TABLE IF EXISTS `' .$table. '`;'.PHP_EOL;

    // Fetch creation query for this table
    $showCreate = $this->pdo('SHOW CREATE TABLE `' .$table. '`', null, false);
    $dump .= $showCreate[1].';'.PHP_EOL;

    // Fetch the table's content
    $tableContent = $this->pdo('SELECT * FROM ' .$table, \PDO::FETCH_ASSOC);

    // Create INSERT lines
    $numberInserts = 0;
    if ($tableContent) {
      $rows = array_keys($tableContent[0]);
      $numberInserts = sizeof($tableContent) - 1;
      $dump .= 'INSERT INTO `' .$table. '` (`' .implode('`, `', $rows). '`) VALUES'.PHP_EOL;

      foreach ($tableContent as $key => $row) {
        $dump .= '("' .implode('","', array_values($row)). '")';
        $dump .= ($key == $numberInserts)
          ? ';' : ','.PHP_EOL;
      }
    }

    return array($dump, $numberInserts);
  }

  /**
   * Clean old saves from the files
   */
  public function cleanup()
  {
    $folders = glob($this->storage.'*');
    foreach($folders as $folder) {
      list($year, $month, $day) = explode('-', basename($folder));
      $month = intval($month);
      $day = intval($day);

      // If dump from last year, remove
      if($year < date('Y')) {
        Directory::remove($folder);
        $this->debug('info', 'Removed save from ' .basename($folder));
        continue;
      } else {
        if($month < date('m') and !in_array($day, array(1, 15))) {
          Directory::remove($folder);
          $this->debug('info', 'Removed save from ' .basename($folder));
          continue;
        }
      }
    }
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////// SETTERS AND GETTERS ////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Switch the display of debug messages
   *
   * @param boolean $debug Display or not
   */
  public function setDebug($debug)
  {
    if(!is_bool($debug)) return false;

    $this->debug = $debug;
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Records a debug message
   *
   * @param  string $type    The alert type (info/error/success)
   * @param  string $message The message
   * @return string          An Alert message
   */
  private function debug($type, $message)
  {
    if(!$this->debug) return false;

    echo call_user_func('\Bootstrapper\Alert::'.$type, $message, false);
  }

  /**
   * Get the full path to a date's folder
   *
   * @param  string $date A date in the format YYYY-mm-dd
   * @return string       A folder path
   */
  private function getFolderForDate($date = null)
  {
    if(!$date) $date = $this->date;

    return $this->storage.$date.'/';
  }

  /**
   * Get the list of tables in the database
   *
   * @return array An array of tables names
   */
  private function tables()
  {
    $tables = array();

    // Fetch results
    $sql = "SHOW TABLES FROM `" .DB::connection()->config['database']. "`";
    $results = $this->pdo($sql);

    // Gather table names
    foreach($results as $result) {
      $tables[] = $result[0];
    }

    return $tables;
  }

  /**
   * Executes a PDO query
   *
   * @param  string   $sql      An SQL query
   * @param  constant $style    A PDO fetching style
   * @param  boolean  $fetchAll Whether we fetch all results or one
   * @return array              An array of results
   */
  private function pdo($sql, $style = null, $fetchAll = true)
  {
    if(!$style) $style = \PDO::FETCH_NUM;

    // Return results
    $results = DB::connection()->pdo->query($sql);
    return $fetchAll ? $results->fetchAll($style) : $results->fetch($style);
  }
}
