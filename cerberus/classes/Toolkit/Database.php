<?php
/**
 *
 * Database
 *
 * Database handling sucks - not with this class :)
 *
 * Configure your database connection like this:
 *
 * <code>
 * c::set('db.host', 'localhost');
 * c::set('db.user', 'root');
 * c::set('db.password', '');
 * c::set('db.name', 'mydb');
 * c::set('db.prefix', '');
 * </code>
 *
 * @package Kirby
 */
namespace Cerberus\Toolkit;

use Cerberus\Core\Config,
    Cerberus\Toolkit\Arrays as a,
    Cerberus\Toolkit\String as str;

class Database
{
  /**
   * State of the current connection
   * @var boolean
   */
  private static $connection = false;

  /**
   * The current database
   * @var string
   */
  private static $database   = null;

  /**
   * The current charset
   * @var string
   */
  private static $charset    = null;

  /**
   * The last query to be executed
   * @var string
   */
  private static $last_query = null;

  /**
   * The number of rows affected by the last query
   * @var integer
   */
  private static $affected   = 0;

  /**
   * A list of all the queries executed
   * @var array
   */
  public static $trace      = array();

  /**
   * \PDO Object holding the current connection
   * @var object
   */
  private static $pdo;

  //////////////////////////////////////////////////////////////////
  /////////////////////////// CONNECTION ///////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
   * Connection to the database with given parameters or with config file
   *
   * @param  string $host     The host to connect to
   * @param  string $user     The SQL user
   * @param  string $password The SQL password
   * @param  string $database The database to connect to
   * @param  string $charset  The charset to set
   * @return mixed  An error if there was one, boolean if it succeeded
   * @package       Cerberus
   */
  public static function connect($host = null, $user = null, $password = null, $database = null, $charset = null)
  {
    // Check if we're not already connected
    if (!self::connection()) {
      $dbhost =
      $dbuser =
      $dbmdp  =
      $dbname = null;

      // Basic keychain
      if (server::local()) {
        $dbhost = 'localhost';
        $dbuser = 'root';
      }
      if(server::host() == 'localhost:8888')
        $dbmdp  = 'root';

      // Gather the connection parameters
      $args     = func_get_args();
      $host     = a::get($args, 0, config::get('db.host',     $dbhost));
      $user     = a::get($args, 1, config::get('db.user',     $dbuser));
      $password = a::get($args, 2, config::get('db.password', $dbmdp));
      $database = a::get($args, 3, config::get('db.name',     $dbname));
      $charset  = a::get($args, 4, config::get('db.charset'));

      if(LOCAL) $password = $dbmdp;

      // Creating \PDO Object for future references
      try {
          self::$pdo = new \PDO('mysql:host=' .$host. ';dbname=' .$database, $user, $password);
          self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        } catch (\PDOException $e) {
          echo $e->getMessage();
        }

      // Try to establish a connection
      self::$connection = @mysql_connect($host, $user, $password);
      if(!self::$connection) throw new Exception(l::get('db.errors.connect'));

      if (self::$connection) {
        // Select the database
        $database = self::database($database);

          // Set the right charset
          $charset = self::charset($charset);
      }
    }
  }

  public static function prepare($sql)
  {
    return self::$pdo->prepare($sql);
  }

  /**
     * Returns the current connection or false
     *
     * @return mixed
     */
  public static function connection()
  {
    return (is_resource(self::$connection)) ? self::$connection : false;
  }

  /**
     * Disconnects from the server
     *
     * @return boolean
     */
  public static function disconnect()
  {
    if(!config::get('db.disconnect')) return false;

    $connection = self::connection();
    if(!$connection) return false;

    // Kill the connection
    $disconnect = @mysql_close($connection);
    self::$connection = false;

    if(!$disconnect) throw new Exception(l::get('db.errors.disconnect'));

    return true;
  }

  /**
     * Selects a database
     *
     * @param  string $database
     * @return mixed
     */
  public static function database($database)
  {
    // If no database has been set
    if(!$database) throw new Exception(l::get('db.errors.missing_db_name'));

    // If we're already connected to it
    if(self::$database == $database) return true;

    // Attempt a connection
    $select = @mysql_select_db($database, self::connection());

    // Display errors if found
    if(!$select) throw new Exception(l::get('db.errors.missing_db'));

    // Else set the current database as the one given
    self::$database = $database;

    return $database;
  }

  /**
     * Sets the charset for all queries
     * The default and recommended charset is utf8
     *
     * @param  string $charset
     * @return mixed
     */
  public static function charset($charset = 'utf8')
  {
    // Check if there is an assigned charset and compare it
    if(self::$charset == $charset) return true;

    // Set the new charset
    $set = self::$pdo->query('SET NAMES ' .$charset);
    if(!$set) throw new Exception(l::get('db.errors.setting_charset_failed', 'Setting database charset failed'));

    // Save the new charset to the globals
    self::$charset = $charset;

    return $charset;
  }

  /**
     * Escapes unwanted stuff in values like slashes, etc.
     *
     * @param  string $value
     * @return string Returns the escaped string
     */
    public static function escape($string)
  {
    if(ctype_digit($string)) $string = intval($string);
    else {
      $string = str::stripslashes($string);
      if (self::connection()) {
        $string = mysql_real_escape_string((string) $string, self::connection());
        $string = addcslashes($string, '%_');
      } else $string = addslashes($string);
    }
    $string = str_replace('\_', '_', $string);

    return $string;
  }

  //////////////////////////////////////////////////////////////////
  //////////////////////////// QUERIES /////////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
     * Runs a MySQL query.
     * You can use any valid MySQL query here.
     * This is also the fallback method if you
     * can't use one of the provided shortcut methods
     * from this class.
     *
     * @param  string  $sql   The sql query
     * @param  boolean $fetch True: apply db::fetch to the result, false: go without db::fetch
     * @return mixed
     */
    public static function query($sql, $fetch = true)
  {
    // Check if the connection is OK
    $connection = self::connection();
    if(!$connection) return $connection;

    // Record last query
    self::$last_query = $sql;
    self::$trace[]    = $sql;

    // Try executing the query
    try {
      $query = self::$pdo->query($sql);
      $results = $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Handle errors
    catch(Exception $e) {
      Debug::handle($e);
    }

    return $results;
  }

  /**
     * Executes a MySQL query without result set.
     * This is used for queries like update, delete or insert
     *
     * @param  string $sql The sql query
     * @return mixed
     */
    public static function execute($sql)
  {
    // Check if the connection is OK
    $connection = self::connection();
    if(!$connection) return $connection;

    // Record last query
    self::$last_query = $sql;
    self::$trace[]    = $sql;

    // Try executing the query
    try {
      self::$affected = self::$pdo->exec($sql);
      $last_id        = self::$pdo->lastInsertId();
    }

    // Handle errors
    catch(Exception $e) {
      Debug::handle($e);
    }

    // Return number of affected rows or ID of insert
    return ($last_id === false) ? self::$affected : self::last_id();
  }

  //////////////////////////////////////////////////////////////////
  //////////////////////////// DIRECTIVES //////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
    * Returns multiple rows from a table
    *
    * @param  string  $table  The table name
    * @param  mixed   $select Either an array of fields or a MySQL string of fields
    * @param  mixed   $where  Either a key/value array as AND connected where clause or a simple MySQL where clause string
    * @param  string  $order  Order clause without the order keyword. ie: "added desc"
  * @param  string  $group  Add a GROUP BY clause without the group by keyword. ie: "field"
    * @param  int     $page   A page number
    * @param  int     $limit  A number for rows to return
    * @param  boolean $fetch  true: apply db::fetch(), false: don't apply db::fetch()
    * @return mixed
    */
  public static function select(
    $table,
    $select = '*',
    $where  = null,
    $order  = null,
    $group  = null,
    $page   = null,
    $limit  = null,
    $fetch  = true)
  {
    if($limit === 0) return array();
    if(is_array($select)) $select = self::select_clause($select);

    $sql = 'SELECT ' .$select. ' FROM ' .self::prefix($table);

    if(!empty($where)) $sql .= ' WHERE ' .self::where($where);
    if(!empty($group)) $sql .= ' GROUP BY ' .$group;
    if(!empty($order)) $sql .= ' ORDER BY ' .$order;
    if($page !== null and $limit !== null) $sql .= ' LIMIT ' .$page. ',' .$limit;

    return self::query($sql, $fetch);
  }

  /**
    * Runs a INSERT query
    *
    * @param  string  $table  The table name
    * @param  mixed   $input  Either a key/value array or a valid MySQL insert string
    * @param  boolean $ignore Set this to true to ignore duplicates
    * @return mixed   The last inserted id if everything went fine or an error response.
    */
  public static function insert($table, $input, $ignore = false)
  {
    $ignore = ($ignore) ? ' IGNORE' : null;

    return self::execute('INSERT' .($ignore). ' INTO ' .self::prefix($table). ' SET ' .self::values($input));
  }

  /**
    * Runs a INSERT query with values
    *
    * @param  string $table The table name
    * @param  array  $fields an array of field names
    * @param  array  $values an array of array of keys and values.
    * @return mixed  The last inserted id if everything went fine or an error response.
    */
  public static function insert_all($table, $fields, $values)
  {
    $fields = ($fields) ? '(' .implode(',', $fields). ')' : null;
    $query = 'INSERT INTO ' .self::prefix($table). ' ' .$fields. ' VALUES ';
    $rows = array();

    foreach ($values as $v) {
      $str = '(\'';
      $sep = '';

      foreach ($v as $input) {
        $str .= $sep.self::escape($input);
        $sep = "','";
      }

      $str .= '\')';
      $rows[] = $str;
    }

    $query .= implode(',', $rows);

    return self::execute($query);
  }

  /**
     * Runs an UPDATE query
     *
     * @param  string $table The table name
     * @param  mixed  $input Either a key/value array or a valid MySQL insert string
     * @param  mixed  $where Either a key/value array as AND connected where clause or a simple MySQL where clause string
     * @return mixed  The number of affected rows or an error response
     */
    public static function update($table, $input, $where, $limit = null)
  {
    return self::execute('UPDATE ' .self::prefix($table). ' SET ' .self::values($input). ' WHERE ' .self::where($where). ' ' .$limit);
  }

  /**
     * Runs a DELETE query
     *
     * @param  string $table The table name
     * @param  mixed  $where Either a key/value array as AND connected where clause or a simple MySQL where clause string
     * @return mixed  The number of affected rows or an error response
     */
    public static function delete($table, $where = null)
  {
    $sql = 'DELETE FROM ' .self::prefix($table);
    if(!empty($where)) $sql .= ' WHERE ' .self::where($where);

    return self::execute($sql);
  }

  /**
     * Runs a REPLACE query
     *
     * @param  string $table The table name
     * @param  mixed  $input Either a key/value array or a valid MySQL insert string
     * @return mixed  The last inserted id if everything went fine or an error response.
     */
  public static function replace($table, $input)
  {
    return self::execute('REPLACE INTO ' .self::prefix($table). ' SET ' .self::values($input));
  }

  /**
     * Joins two tables and returns data from them
     *
     * @param  string $table_1 The table name of the first table
     * @param  string $table_2 The table name of the second table
     * @param  string $on      The MySQL ON clause without the ON keyword. ie: "user_id = comment_user"
     * @param  mixed  $select  Either an array of fields or a MySQL string of fields
     * @param  mixed  $where   Either a key/value array as AND connected where clause or a simple MySQL where clause string
     * @param  string $order   Order clause without the order keyword. ie: "added desc"
     * @param  int    $page    A page number
     * @param  int    $limit   A number for rows to return
     * @param  string $type    The join type (JOIN, LEFT, RIGHT, INNER)
     * @return mixed
     */
    public static function join($table_1, $table_2, $on, $select, $where = null, $order = null, $group = null, $page = null, $limit = null, $type = 'JOIN')
  {
    return self::select(
      self::prefix($table_1). ' ' .$type. ' ' .
      self::prefix($table_2). ' ON ' .
      self::where($on),
      $select,
      self::where($where),
      $order,
      $group,
      $page,
      $limit
    );
  }

  /**
    * Runs a LEFT JOIN
    *
    * @param  string  $table_1 The table name of the first table
    * @param  string  $table_2 The table name of the second table
    * @param  string  $on The MySQL ON clause without the ON keyword. ie: "user_id = comment_user"
    * @param  mixed   $select Either an array of fields or a MySQL string of fields
    * @param  mixed   $where Either a key/value array as AND connected where clause or a simple MySQL where clause string
    * @param  string  $order Order clause without the order keyword. ie: "added desc"
    * @param  int     $page a page number
    * @param  int     $limit a number for rows to return
    * @return mixed
    */
  public static function left_join($table_1, $table_2, $on, $select, $where = null, $order = null, $page = null, $limit = null)
  {
      return self::join($table_1, $table_2, $on, $select, $where, $order, $page, $limit, 'LEFT JOIN');
  }

  /**
    * Counts a number of rows in a table
    *
    * @param  string  $table The table name
    * @param  mixed   $where Either a key/value array as AND connected where clause or a simple MySQL where clause string
    * @return int
    */
  public static function count($table, $where = '')
  {
    $result = self::row($table, 'count(*)', $where);

    return ($result) ? a::get($result, 'count(*)') : 0;
  }

  /**
     * Gets the minimum value in a column of a table
     *
     * @param  string $table  The table name
     * @param  string $column The name of the column
     * @param  mixed  $where  Either a key/value array as AND connected where clause or a simple MySQL where clause string
     * @return mixed
     */
    public static function min($table, $column, $where = null)
  {
    $sql = 'SELECT MIN(' .$column. ') as min FROM ' .self::prefix($table);
    if(!empty($where)) $sql .= ' WHERE ' .self::where($where);

    $result = self::query($sql, false);
    $result = self::fetch($result);

    return a::get($result, 'min', 1);
  }

  /**
     * Gets the maximum value in a column of a table
     *
     * @param  string $table  The table name
     * @param  string $column The name of the column
     * @param  mixed  $where  Either a key/value array as AND connected where clause or a simple MySQL where clause string
     * @return mixed
     */
    public static function max($table, $column, $where = null)
  {
    $sql = 'SELECT MAX(' .$column. ') as max FROM ' .self::prefix($table);
    if(!empty($where)) $sql .= ' WHERE ' .self::where($where);

    $result = self::query($sql, false);
    $result = self::fetch($result);

    return a::get($result, 'max', 1);
  }

  /**
     * Gets the sum of values in a column of a table
     *
     * @param  string $table  The table name
     * @param  string $column The name of the column
     * @param  mixed  $where  Either a key/value array as AND connected where clause or a simple MySQL where clause string
     * @return mixed
     */
    public static function sum($table, $column, $where = null)
  {
    $sql = 'SELECT SUM(' .$column. ') as sum FROM ' .self::prefix($table);
    if(!empty($where)) $sql .= ' WHERE ' .self::where($where);

    $result = self::query($sql, false);
    $result = self::fetch($result);

    return a::get($result, 'sum', 0);
  }

  /**
     * Adds a prefix to a table name if set in c::set('db.prefix', 'myprefix_');
     * This makes it possible to use table names in all methods without prefix
     * and it will still be applied automatically.
     *
     * @param  string $table The name of the table with or without prefix
     * @return string The sanitized table name.
     */
    public static function prefix($table)
  {
    $prefix = config::get('db.prefix');
    if(!$prefix) return $table;
    else return (!str::contains($table, $prefix)) ? $prefix.$table : $table;
  }

  /**
   * Deletes a table from the database
   *
   * @param  string  $table The name of the table
   * @return boolean Success of the operation
   * @package        Cerberus
   */
  public static function drop($table)
  {
    return db::execute('DROP TABLE IF EXISTS `' .$table. '`;');
  }

  //////////////////////////////////////////////////////////////////
  //////////////////////////// PARTIAL RESULTS //////////////////////
  //////////////////////////////////////////////////////////////////

  /**
    * Returns a single row from a table
    *
    * @param  string  $table  The table name
    * @param  mixed   $select Either an array of fields or a MySQL string of fields
    * @param  mixed   $where  Either a key/value array as AND connected where clause or a simple MySQL where clause string
    * @param  string  $order  Order clause without the order keyword. ie: "added desc"
    * @return mixed
    */
  public static function row($table, $select = '*', $where = null, $order = null)
  {
    $result = self::select($table, $select, $where, $order, null, 0, 1, false);

    return a::get($result, 0);
  }

  /**
    * Returns a single field value from a table
    *
    * @param  string  $table The table name
    * @param  string  $field The name of the field
    * @param  mixed   $where Either a key/value array as AND connected where clause or a simple MySQL where clause string
    * @param  string  $order Order clause without the order keyword. ie: "added desc"
    * @return mixed
    */
  public static function field($table, $field, $where = null, $order = null)
  {
    $result = self::row($table, $field, $where, $order);

    return a::get($result, $field);
  }

  /**
    * Returns all values from single column of a table
    *
    * @param  string  $table  The table name
    * @param  string  $column The name of the column
    * @param  mixed   $where  Either a key/value array as AND connected where clause or a simple MySQL where clause string
    * @param  string  $order  Order clause without the order keyword. ie: "added desc"
    * @param  int     $page   A page number
    * @param  int     $limit  A number for rows to return
    * @return mixed
    */
  public static function column($table, $column, $where = null, $order = null, $page = null, $limit = null)
  {
    $result = self::select($table, $column, $where, $order, null, $page, $limit, false);
    $array = array();
    foreach($result as $r) array_push($array, a::get($r, $column));

    return $array;
  }

  //////////////////////////////////////////////////////////////////
  //////////////////////////// HELPERS /////////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
   * Shows the different tables in the database
     *
     * @return array The different tables in the database
     * @package      Cerberus
     */
  public static function showtables()
  {
    $tables = self::query('SHOW TABLES', true);
    $tables = a::simplify($tables, false);

    return $tables;
  }

  /**
     * Returns an array of fields in a given table
     *
     * @param  string $table The table name
     * @return array  The array of field names
     */
  public static function fields($table)
    {
    if (self::is_table($table)) {
      $fields = @mysql_list_fields(self::$database, self::prefix($table), self::$connection);
      if(!$fields) return self::error(l::get('db.errors.fields'));

      $count  = @mysql_num_fields($fields);
      for($x = 0; $x < $count; $x++)
        $output[] = @mysql_field_name($fields, $x);

      return $output;
    } else return array();
  }

  /**
   * Checks if one or more given tables exist in the database
   *
   * @param array   $tables The tables to search for
   * @param boolean $detail In case of multiple tables in the first parameter
   *                          true: returns the existence of each table
   *                          false: returns a boolean stating if all or none of the table exist
   * @return mixed  A boolean if $detail is false, an array of booleans if it's true
   * @package       Cerberus
   */
  public static function is_table($tables, $detail = false)
  {
    // $tables = func_get_args();
    if(!is_array($tables)) $tables = array($tables);

    if(sizeof($tables) == 1)

      return in_array($tables[0], self::showtables());

    else {
      $found = 0;
      $return = array();

      foreach ($tables as $table) {
        $exists = in_array($table, self::showtables());
        if($detail) $return[$table] = $exists;
        else if($exists) $found++;
      }

      return $detail ? $return : ($found == sizeof($tables));
    }
  }

  /**
     * Checks wether a field exists in a table
     *
     * @param  string  $field The field to search for
     * @param  string  $table The table to search in
     * @return boolean A boolean stating if the table exists
     * @package        Cerberus
     */
  public static function is_field($field, $table)
  {
    return in_array($field, self::fields($table));
  }

  /**
   * Returns the number of affected rows for the last query
   *
   * @return int
   */
  public static function affected()
  {
    return self::$affected;
  }

  /**
   * Returns the last returned insert id
   *
   * @return int
   */
  public static function last_id()
  {
    $connection = self::connection();

    return @mysql_insert_id($connection);
  }

  /**
   * Returns the last query exectued
   *
   * @return string
   */
  public static function last_sql()
  {
    str::display(end(self::$trace));
  }

  /**
   * Displays a message according to the success of last query
   *
   * @param  string   $true   If the query was successful
   * @param  string   $false  If it wasn't
   * @param  boolean  $format Wrap the status in an error/success block
   * @return string   The status
   * @package         Cerberus
   */
  public static function status($true, $false, $format = true)
  {
    $return = self::$affected >= 0 ? $true : $false;
    if($format) str::display($return);

    return $return;
  }
  /**
   * Builds responses according to certain keywords and query status
   *
   * @param  string  $many           The term for "many" items
   * @param  string  $one            The term for "one" item
   * @param  string  $zero           The term for "none" item
   * @param  string  $action         The action corresponding to one item
   * @param  string  $action_plural  The action for several items
   * @package        Cerberus
   */
  public static function status_this($many, $one, $zero = null, $action, $action_plural = null)
  {
    // If not action is set for several item, use the singular
    if(!$action_plural) $action_plural = $action;

    // If we have more than one row affected, switch to plural
    if(self::$affected > 1) $action = $action_plural;

    // Select the right term
    $terme = str::plural(
      self::$affected,
      self::$affected. ' ' .$many,
      $one,
      $zero);

    // Display message according to success/error
    if(self::$affected) str::display($terme. ' ' .$action, 'success');
    else                str::display($terme. ' ' .$action, 'error');
  }

  /**
    * Returns the next ID to be in the table
    *
    * @return int The next ID in Auto Increment
    */
  public static function increment($table)
  {
    $result = db::query('SHOW TABLE STATUS LIKE "' .$table. '"');
    $result = $result[0];

    return a::get($result, 'Auto_increment');
  }

  //////////////////////////////////////////////////////////////////
  /////////////////////// CORE AND BUILDERS ////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
     * Shortcut for mysql_fetch_array
     *
     * @param  resource $result The unfetched result from db::query()
     * @param  const    $type   PHP flag for mysql_fetch_array
     * @return array    The key/value result array
     */
  public static function fetch($result, $type = MYSQL_ASSOC)
  {
    if(!$result) return array();

    return @mysql_fetch_array($result, $type);
  }

  /**
     * Builds a select clause from a simple array
     *
     * @param  array  $field An array of field names
     * @return string The MySQL string
     */
  public static function select_clause($fields)
  {
    return is_array($fields) ? implode(', ', $fields) : $fields;
  }

  /**
     * A simplifier to build search clauses
     *
     * @param  string $search The search word
     * @param  array  $fields An array of fields to search
     * @param  string $mode   OR or AND
     * @return string Returns the final where clause
     */
  public static function search_clause($search, $fields, $mode = 'OR')
  {
    if(empty($search)) return false;

    $arr = array();
    foreach($fields as $f)
      array_push($arr, $f.' LIKE \'%'.$search.'%\'');

    return '('.implode(' '.trim($mode).' ', $arr).')';
  }

  /**
     * An easy method to build a part of the where clause to find stuff by its first character
     *
     * @param  string $field The name of the field
     * @param  string $char  The character to search for
     * @return string Returns the where clause part
     */
  public static function with($field, $char)
  {
    return 'LOWER(SUBSTRING('.$field.',1,1)) = "'.db::escape($char).'"';
  }

  /**
     * A simplifier to build IN clauses
     *
     * @param  array  $array An array of fieldnames
     * @return string The MySQL string for the where clause
     */
  public static function in($array)
  {
    return '\'' .implode('\',\'', $array). '\'';
  }

  /**
    * A handler to convert key/value arrays to an where clause
    *
    * You can specify modifiers appened to the keys to use special SQL functions.
    * The syntax is the following : 'key1[MODIFIER]' => 'value1', ie: 'key1>=' => '5' will output key1 >= 5
    * Most modifiers will just come and replace the =, with the exception of ? and ?? that will
    * invoke a LIKE comparaison, with ?? being for using a Regex as value (it will prevent escaping it)
    *
    * @param  array  $array  keys/values for the where clause
    * @param  string $method AND or OR
    * @return string The MySQL string for the where clause
    */
  public static function where($array, $method='AND')
  {
    if(!is_array($array)) return $array;

    $output = array();
    foreach ($array as $field => $value) {
      $modifiers = array('>', '<', '?', '!=', '>=', '<=', '??');
      $modifier = '=';
      $modifier_multiple = 'IN';

      foreach($modifiers as $m)
        if(str::find($m, $field)) $modifier = $m;
      $field = str_replace($modifier, null, $field);

      switch ($modifier) {
        case '!=':
        $modifier_multiple = 'NOT IN';
        break;

        case '??':
        $regex = true;
        $modifier = 'LIKE';
        break;

        case '?':
        $modifier = 'LIKE';
        break;
      }

      // Escaping
      if(!is_array($value))
        if(!isset($regex)) $value = self::escape($value);

      // Exceptions for aliases and SQL functions
      $field = (str::find('.', $field) or str::find('(', $field)) ? $field :  '`' .$field. '`';

      if(is_string($value)) $output[] = $field. ' ' .$modifier. ' \'' .$value. '\'';
      else if(is_array($value)) $output[] = $field. ' ' .$modifier_multiple. ' ("' .implode('","', $value). '")';
      else $output[] = $field. ' ' .$modifier. ' ' .$value. '';

      $separator = ' ' .$method. ' ';
    }

    return implode(' ' . $method . ' ', $output);
  }

  /**
     * Makes it possible to use arrays for inputs instead of MySQL strings
     *
     * @param  array  $input The values to input
     * @return string The final MySQL string, which will be used in the queries.
     */
  public static function values($input)
  {
    if(!is_array($input)) return $input;

    $output = array();
    foreach ($input as $key => $value) {
      if($value === 'NOW()')
        $output[] = $key. ' = NOW()';

      elseif(is_array($value))
        $output[] = $key. ' = \'' .a::json($value). '\'';

      else
        $output[] = $key. ' = \'' .self::escape($value). '\'';
    }

    return implode(', ', $output);
  }

  /**
     * An internal error handler
     *
     * @param  string  $msg  The error/success message to return
     * @param  boolean $exit Die after this error?
     * @return mixed
     */
  public static function error($message = null, $exit = false)
  {
    $connection = self::connection();
    $error = (mysql_error() and $connection) ? @mysql_error($connection) : false;

    if(self::$last_query and LOCAL) str::display(htmlentities(self::$last_query), 'error');
    if($error) errorHandle('SQL', $error, __FILE__, __LINE__);

    if($exit or !LOCAL) die($message);
  }

  /**
     * Strips table specific column prefixes from the result array
     *
     * If you use column names like user_username, user_id, etc.
     * use this method on the result array to strip user_ of all fields
     *
     * @param  array $array The result array
     * @return array The result array without those damn prefixes.
     */
  public static function simple_fields($array)
  {
    if(empty($array)) return false;

    $output = array();
    foreach ($array as $key => $value) {
      $key = substr($key, strpos($key, '_') + 1);
      $output[$key] = $value;
    }

    return $output;
  }
}
