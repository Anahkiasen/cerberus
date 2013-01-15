<?php
namespace Cerberus\Backup;

use \Underscore\Types\Arrays;

class Explainer
{
  /**
   * A list of ignored tables
   * @var array
   */
  private $ignored = array(
    'migrations',
  );

  /**
   * Get the list of tables in the database
   *
   * @return array An array of tables names
   */
  protected function getTables()
  {
    // Get database name
    $database = $this->connection->getDatabaseName();
    $database = basename($database);

    // Create query
    switch ($this->connection->getDriverName()) {
      case 'mysql':
        $results = $this->pdo("SHOW TABLES FROM `" .$database. "`");
        $results = Arrays::pluck($results, 0);
        break;
      case 'sqlite':
        $results = $this->app['db']->table('sqlite_master')->where('type', 'table')->get();
        $results = Arrays::pluck($results, 'name');
        break;
    }

    return $results;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// VIVISECTORS //////////////////////////
  ////////////////////////////////////////////////////////////////////

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
    $dump .= $this->explainTable($table);

    // Fetch the table's content
    $tableContent = $this->dumpContentsOf($table);
    var_dump($dump);
    var_dump($tableContent);
    exit();
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
   * Returns the schema for a table
   *
   * @param string $table The table name
   * @return string An SQL schema
   */
  protected function explainTable($table)
  {
    switch ($this->connection->getDriverName()) {
      case 'mysql':
        $sql = $this->pdo('SHOW CREATE TABLE `' .$table. '`', null, false);
        $sql = $sql[0]. ';'.PHP_EOL;
        break;
      case 'sqlite':
        $sql = $this->app['db']->table('sqlite_master')->where('name', $table)->first(array('sql'));
        $sql = $sql->sql;
        break;
    }

    return $sql.';'.PHP_EOL;
  }

  /**
   * Returns the content of a table in SQL
   *
   * @param string $table The table name
   * @return string A list of SQL inserts
   */
  protected function dumpContentsOf($table)
  {
    // Fetch the table's content
    $tableContent = $this->pdo('SELECT * FROM ' .$table, \PDO::FETCH_ASSOC);
    $dump = null;

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

    return $dump;
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////////// HELPERS ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Executes a PDO query
   *
   * @param  string   $sql      An SQL query
   * @param  constant $style    A PDO fetching style
   * @param  boolean  $fetchAll Whether we fetch all results or one
   * @return array              An array of results
   */
  protected function pdo($sql, $style = \PDO::FETCH_NUM, $fetchAll = true)
  {
    // Return results
    $results = $this->connection->getPdo()->query($sql);

    return $fetchAll ? $results->fetchAll($style) : $results->fetch($style);
  }
}
