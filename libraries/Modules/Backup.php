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
    Cerberus\Toolkit\String,
    Laravel\Lang,
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
   * Save the database for the given date
   */
  public function save()
  {
    $tables = $this->tables();
    $unsavedTables = array();

    // If we have tables to save
    if ($tables) {

      // Get database name
      $database = DB::connection()->config['database'];
      $this->debug('info', 'save_database', array('database' => $database));

      // Read dumps for current date
      $dumps = $this->getFolderForDate($this->date);
      $numberDumps = file_exists($dumps) ? sizeof(glob($dumps.'*')) : 0;
      if ($numberDumps > 0) {
        $this->debug(
          'success',
          'dump_exists',
           array('date' => $this->date, 'nbdumps' => $numberDumps));

        return $this;
      }

      // If no dumps, save tables
      $folder = $this->getFolderForDate();
      foreach ($tables as $table) {
        $filepath = $folder.$table.'_'.$this->date.date('@H-m-s').'.sql';
        list($export, $numberRows) = $this->exportTable($table);

        $write = File::write($filepath, $export);
        if ($write) {
          $this->debug('success','table_saved', array('table' => $table, 'nbrows' => $numberRows));
        } else {
          $unsavedTables[] = $table;
          $this->debug('error', 'error_saving_table', array('table' => $table));
        }
      }

      // Make sure all tables were correctly saved
      if (empty($unsavedTables)) $this->debug('success', 'database_saved', array('database' => $database));
      else  $this->debug('error', 'tables_unsaved', array('tables' => implode(', ', $unsavedTables)));
    } else $this->debug('info', 'No tables to save');

    return $this;
  }

  /**
   * Load the currently selected SQL dump
   *
   * @param  string  $date The date to load
   * @return boolean       Whether the loading was successful or not
   */
  public function load($date = null)
  {
    // If date was specified, change it
    if($date) $this->setDate($date);

    // Fetch all dumps from the date
    $dumps  = $this->readDumps();
    $pdo    = DB::connection()->pdo;

    foreach ($dumps as $dump) {

      // Separate statements into array entries
      $sql = trim($dump['content']);
      $table = $pdo->quote($dump['table']);
      $statements = array_filter(explode(';', $sql));

      // Execute the current statement
      foreach ($statements as $key => $statement) {
        $statement = trim($statement);
        $results = $pdo->exec($statement);

        // Display corresponding message
        switch ($key) {
          case 1:
            $this->debug('success', 'table_loaded', array('table' => $table));
            break;
          case 2:
            $this->debug('info', 'entries_loaded', array('table' => $table, 'entries' => $results));
            break;
        }
      }

      // If the table was empty, say it
      if (!isset($statements[2])) {
        $this->debug('info', 'no_entries', array('table' => $table));
      }
    }
    $this->debug('success', 'dump_loaded', array('date' => $this->date));

    return $this;
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
    $dumps = array_map(array('self', 'parseDump'), $dumps);

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
    $this->debug('info', 'cleaning');
    $folders = glob($this->storage.'*');
    foreach ($folders as $folder) {
      $date = basename($folder);
      list($year, $month, $day) = explode('-', $date);
      $month = intval($month);
      $day = intval($day);

      // If dump from last year, remove
      if ($year < date('Y')) {
        Directory::remove($folder);
        $this->debug('info', 'dump_removed', array('date' => $date));
        continue;
      } else {
        if ($month < date('m') and !in_array($day, array(1, 15))) {
          Directory::remove($folder);
          $this->debug('info', 'dump_removed', array('date' => $date));
          continue;
        }
      }
    }

    return $this;
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

    return $this;
  }

  /**
   * Change the current date in use
   *
   * @param string $date A date formatted YYYY-mm-dd
   */
  public function setDate($date)
  {
    // If given timestamp, parse it to date
    if(!String::find('-', $date)) $date = date('Y-m-d', $date);

    $this->date = $date;

    return $this;
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

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
      'content' => File::get($dump),
      'date'    => date('Y-m-d', $unix),
      'hour'    => date('H:i:s', $unix),
      'table'   => $matches[1],
      'unix'    => $unix,
    );
  }

  /**
   * Records a debug message
   *
   * @param  string $type         The alert type (info/error/success)
   * @param  string $message      The message
   * @param  array  $replacements Translation replacements
   * @return string               An Alert message
   */
  private function debug($type, $message, $replacements = array())
  {
    if(!$this->debug) return false;

    $message = Lang::line('cerberus::backup.'.$message, $replacements)->get();

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
    foreach ($results as $result) {
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
  private function pdo($sql, $style = \PDO::FETCH_NUM, $fetchAll = true)
  {
    // Return results
    $results = DB::connection()->pdo->query($sql);

    return $fetchAll ? $results->fetchAll($style) : $results->fetch($style);
  }
}
