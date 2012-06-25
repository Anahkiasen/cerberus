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
 * @package Kirby, Cerberus
 */
class pd
{
	/**
	 * State of the current connection
	 * @var boolean
	 */
	private static $connection = false;

	/**
	 * PDO Object holding the current connection
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
		if(!self::connection())
		{
			$dbhost =
			$dbuser =
			$dbmdp  =
			$dbname = null;

			// Basic keychain
			if(server::local())
			{
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

			// Creating PDO Object for future references
			try
			{
				self::$pdo = new PDO('mysql:host=' .$host. ';dbname=' .$database, $user, $password);
				self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch(PDOException $e)
			{
				$message = null;
				switch($e->getCode())
				{
					case '1049':
						$message = l::get('db.errors.missing_db');
						break;

					case '2005':
						$message = l::get('db.errors.host');
						break;
				}
				Debug::handle($e, $message);
				if($message) str::display($message, 'error');
			}
		}
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

}
