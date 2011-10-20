<?php
class db
{
	private static 	$connection = false;
	private static 	$database	= false;
	private static 	$charset	= false;
	private static	$last_query = false;
	public	static 	$trace		= array();
	private static 	$affected 	= 0;

	/*
	########################################
	############### CONNEXION ##############
	########################################
	*/

	static function connect()
	{
		$connection	= self::connection();
		
		// Connexion
		if(!$connection)
		{
			// Trousseau d'accès
			if(server::get('HTTP_HOST') == 'localhost:8888')
			{
				// Local MAMP
				$dbhost = 'localhost';
				$dbuser = 'root';
				$dbmdp = 'root';
				$dbname = NULL;
			}
			elseif(server::get('HTTP_HOST') == '127.0.0.1')
			{
				// Local EasyPHP
				$dbhost = 'localhost';
				$dbuser = 'root';
				$dbmdp = NULL;
				$dbname = NULL;
			}
			elseif(server::get('HTTP_HOST') == 'the8day.info')
			{
				// Le Huitième Jour
				$dbhost = 'db124.1and1.fr';
				$dbuser = 'dbo144396219';
				$dbmdp = 'naxam35741';
				$dbname = 'db144396219';
			}
			elseif(server::get('HTTP_HOST') == 'stappler.fr' or $_SERVER['HTTP_HOST'] == 'www.stappler.fr')
			{
				// Stappler
				$dbhost = 'hostingmysql51';
				$dbuser = '859841_maxime';
				$dbmdp = NULL;
				$dbname = 'MAXSTA001';
			}

			$args		= func_get_args();
			$host		= a::get($args, 0, config::get('db.host', $dbhost));
			$user		= a::get($args, 1, config::get('db.user', $dbuser));
			$password	= a::get($args, 2, config::get('db.password', $dbmdp));
			$database	= a::get($args, 3, config::get('db.name', $dbname));
			
			$connection = @mysql_connect($host, $user, $password);
			self::$connection = $connection;
			$database = self::database($database);
			mysql_query("SET NAMES 'utf8'");
		}

		// Affichage des erreurs
		if(!$connection) return self::error(l::get('db.errors.connect', 'Erreur de connexion à MySQL'), true);
		else return $connection;
	}
	
	// Connexion à la base de données
	static function database($database)
	{
		if(!$database) return self::error(l::get('db.errors.missing_db_name', 'Pas de base séléctionnée'), true);
		else
		{
			if(self::$database == $database) return true;
			else
			{
				$select = @mysql_select_db($database, self::connection());
				if(!$select) return self::error(l::get('db.errors.missing_db', 'Erreur de connexion à la base'), true);
				self::$database = $database;
				return $database;
			}
		}
	}
	
	static function connection()
	{
		return (is_resource(self::$connection)) ? self::$connection : FALSE;
	}
	
	// Convertit une chaîne en chaîne sûre
	static function escape($string)
	{
		if(ctype_digit($string)) $string = intval($string);
		else
		{
			$string = str::stripslashes($string);
			$string = mysql_real_escape_string((string)$string, self::connect());
			$string = addcslashes($string, '%_');
		}
		return $string;
	}
		
	/*
	########################################
	############### REQUÊTES ###############
	########################################
	*/
	
	// Exécute une requête fetch
	static function query($sql, $fetch = true)
	{
		$connection = self::connect();
		self::$last_query = $sql;
		
		$result = @mysql_query($sql);
		self::$affected = @mysql_affected_rows();
		self::$trace[] = $sql;

		if(!$result) self::error(l::get('db.errors.query', 'Requête incorrecte'));
		if(!$fetch)	return $result;

		$array = array();
		while($r = self::fetch($result)) array_push($array, $r);
		return $array;
	}
	
	// Exécute une requête inline
	static function execute($sql)
	{
		$connection = self::connect();
		self::$last_query = $sql;

		
		$execute = @mysql_query($sql, $connection);
		self::$affected = @mysql_affected_rows();
		self::$trace[] = $sql;

		if(!$execute) self::error(l::get('db.errors.execute', 'Requête incorrecte'));
		
		$last_id = self::last_id();
		return ($last_id === false) ? self::$affected : self::last_id();
	}

	// Effectue une requête SELECT
	static function select($table, $select = '*', $where = NULL, $order = NULL, $page = NULL, $limit = NULL, $fetch = TRUE)
	{
		$sql = 'SELECT ' .$select. ' FROM ' .self::prefix($table);

		if(!empty($where)) $sql .= ' WHERE ' . self::where($where);
		if(!empty($order)) $sql .= ' ORDER BY ' .$order;
		if($page !== NULL and $limit !== NULL) $sql .= ' LIMIT ' .$page. ',' .$limit;

		return self::query($sql, $fetch);
	}
	
	// Execute une requête INSERT
	static function insert($table, $input, $ignore = false)
	{
		$ignore = ($ignore) ? ' IGNORE' : '';
		return self::execute('INSERT' .($ignore). ' INTO ' .self::prefix($table). ' SET ' .self::values($input));
	}
	
	// Requête UPDATE
	static function update($table, $input, $where, $limit = NULL)
	{
		return self::execute('UPDATE ' .self::prefix($table). ' SET ' .self::values($input). ' WHERE ' .self::where($where). ' ' .$limit);
	}
	
	/*
	########################################
	############### RACCOURCIS #############
	########################################
	*/

	// Ne renvoit que le premier résultat
	static function row($table, $select = '*', $where = NULL, $order = NULL)
	{
		$result = self::select($table, $select, $where, $order, 0, 1, false);
		return self::fetch($result);
	}
	
	// Ne renvoit qu'un seul champ du premier résultat
	static function field($table, $field, $where = NULL, $order = NULL)
	{
		$result = self::row($table, $field, $where, $order);
		return a::get($result, $field);
	}
	
	// Compte le nombre d'entrées
	static function count($table, $where = '')
	{
		$result = self::row($table, 'count(*)', $where);
		return ($result) ? a::get($result, 'count(*)') : 0;
	}

	/*
	########################################
	############### MOTEUR SQL #############
	########################################
	*/
	
	// Place les résultats SQL dans un array
	static function fetch($result)
	{
		if(!$result) return array();
		else return @mysql_fetch_assoc($result);
	}
	
	// Transforme un array en syntaxe WHERE
	static function where($array, $method = 'AND')
	{
		if(!is_array($array)) return $array;
		else
		{
			$output = array();
			foreach($array as $field => $value)
			{
				$output[] = $field. ' = \'' .self::escape($value). '\'';
				$separator = ' ' .$method. ' ';
			}
			return implode(' ' . $method. ' ', $output);
		}
	}
		
	// Transforme un array en syntaxe UPDATE/INSERT
	static function values($input)
	{
		if(!is_array($input)) return $input;

		$output = array();
		foreach($input as $key => $value)
		{
			if($value === 'NOW()')
				$output[] = $key. ' = NOW()';
			
			elseif(is_array($value))
				$output[] = $key. ' = \'' . a::json($value) . '\'';
			
			else
				$output[] = $key. ' = \'' . self::escape($value) . '\'';
		}
		return implode(', ', $output);
	}
	
	// Ajoute un préfixe à la table si configuré
	static function prefix($table)
	{
		$prefix = config::get('db.prefix');
		if(!$prefix) return $table;
		else return (!str::contains($table, $prefix)) ? $prefix.$table : $table;
	}
		
	// Gère les erreurs
	static function error($message = NULL, $exit = FALSE)
	{
		$connection = self::connection();
		$error = (mysql_error()) ? @mysql_error($connection) : false;
		
		if(!PRODUCTION)
		{
			if(self::$last_query) echo display(htmlentities(self::$last_query));
			echo errorHandle('SQL', $error, __FILE__, __LINE__);
		}
		else $message .= ' - ' .l::get('db.error', 'Une erreur SQL est survenue');
		
		if($exit or PRODUCTION) die($message);
	}

	/*
	########################################
	############### UTILITAIRES ############
	########################################
	*/

	// Affiche la liste des tables
	static function showtables()
	{
		$tables = self::query('SHOW TABLES', TRUE);
		return a::simple($tables);
	}

	// Liste les champs d'une table
	static function fields($table)
	{
		$connection = self::connect();

		$fields = @mysql_list_fields(self::$database, self::prefix($table), $connection);
		if(!$fields) return self::error(l::get('db.errors.fields', 'Impossible de lister les champs'));

		$count	= @mysql_num_fields($fields);
		for($x = 0; $x < $count; $x++)
			$output[] = @mysql_field_name($fields, $x);
		
		return $output;
	}

	// Vérifie si une table existe
	static function is_table($table)
	{
		return (in_array($table, self::showtables()));
	}
	
	// Retourne le nombre d'entrées afféctées
	static function affected()
	{
		return self::$affected;
	}

	// Retourne l'id de la dernière requête
	static function last_id()
	{
		$connection = self::connection();
		return @mysql_insert_id($connection);
	}




// ESDFGDG









	static function disconnect()
	{
		if(!config::get('db.disconnect')) return false;

		$connection = self::connection();
		if(!$connection) return false;

		// kill the connection
		$disconnect = @mysql_close($connection);
		self::$connection = false;

		if(!$disconnect) return self::error(l::get('db.errors.disconnect', 'Disconnecting database failed'));
		return true;
	}

	static function insert_all($table, $fields, $values)
	{
			
		$query = 'INSERT INTO ' . self::prefix($table) . ' (' . implode(',', $fields) . ') VALUES ';
		$rows  = array();
		
		foreach($values as $v) {    
			$str = '(\'';
			$sep = '';
			
			foreach($v as $input) {
				$str .= $sep . self::escape($input);            
				$sep = "','";  
			}

			$str .= '\')';
			$rows[] = $str;
		}
		
		$query .= implode(',', $rows);
		return self::execute($query);
	
	}

	static function replace($table, $input)
	{
		return self::execute('REPLACE INTO ' . self::prefix($table) . ' SET ' . self::values($input));
	}

	static function delete($table, $where = '')
	{
		$sql = 'DELETE FROM ' . self::prefix($table);
		if(!empty($where)) $sql .= ' WHERE ' . self::where($where);
		return self::execute($sql);
	}


	static function column($table, $column, $where = NULL, $order = NULL, $page = NULL, $limit = NULL) {

		$result = self::select($table, $column, $where, $order, $page, $limit, false);

		$array = array();
		while($r = self::fetch($result)) array_push($array, a::get($r, $column));
		return $array;
	}

	static function join($table_1, $table_2, $on, $select, $where = NULL, $order = NULL, $page = NULL, $limit = NULL, $type="JOIN") {
			return self::select(
				self::prefix($table_1) . ' ' . $type . ' ' .
				self::prefix($table_2) . ' ON ' .
				self::where($on),
				$select,
				self::where($where),
				$order,
				$page,
				$limit
			);
	}

	static function left_join($table_1, $table_2, $on, $select, $where = NULL, $order = NULL, $page = NULL, $limit = NULL) {
			return self::join($table_1, $table_2, $on, $select, $where, $order, $page, $limit, 'LEFT JOIN');
	}

	static function min($table, $column, $where = NULL) {

		$sql = 'SELECT MIN(' . $column . ') as min FROM ' . self::prefix($table);
		if(!empty($where)) $sql .= ' WHERE ' . self::where($where);

		$result = self::query($sql, false);
		$result = self::fetch($result);

		return a::get($result, 'min', 1);

	}

	static function max($table, $column, $where = NULL) {

		$sql = 'SELECT MAX(' . $column . ') as max FROM ' . self::prefix($table);
		if(!empty($where)) $sql .= ' WHERE ' . self::where($where);

		$result = self::query($sql, false);
		$result = self::fetch($result);

		return a::get($result, 'max', 1);

	}

	static function sum($table, $column, $where = NULL) {

		$sql = 'SELECT SUM(' . $column . ') as sum FROM ' . self::prefix($table);
		if(!empty($where)) $sql .= ' WHERE ' . self::where($where);

		$result = self::query($sql, false);
		$result = self::fetch($result);

		return a::get($result, 'sum', 0);

	}


	static function simple_fields($array)
	{
		if(empty($array)) return false;
		$output = array();
		foreach($array as $key => $value)
		{
			$key = substr($key, strpos($key, '_')+1);
			$output[$key] = $value;
		}
		return $output;
	}


	static function search_clause($search, $fields, $mode='OR') {

		if(empty($search)) return false;

		$arr = array();
		foreach($fields as $f) array_push($arr, $f . ' LIKE \'%' . $search . '%\'');
		return '(' . implode(' ' . trim($mode) . ' ', $arr) . ')';

	}

	static function select_clause($fields) {
		return implode(', ', $fields);
	}

	static function in($array) {
		return '\'' . implode('\',\'', $array) . '\'';
	}
}
?>