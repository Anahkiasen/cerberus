<?php
// Gestion des erreurs
include('tools/errorHandle.php');
date_default_timezone_set('Europe/Paris');
ini_set('error_log', 'cerberus/cache/error.log');
ini_set('log_errors', 'On');

// Chemins principaux
if(!defined('PATH_MAIN')) define('PATH_MAIN', '');
if(!defined('PATH_CONF')) define('PATH_CONF', PATH_MAIN.'cerberus/conf.php');

// Chargement du moteur Cerberus
include(PATH_MAIN.'cerberus/class/kirby.request.php');
function __class_loader($class_name) 
{
	$class_name = str_replace('_', '.', strtolower($class_name));
	$file = glob(PATH_MAIN.'cerberus/class/{kirby,class,core,kirby.plugins}.' .$class_name. '*.php', GLOB_BRACE);
	if($file and file_exists($file[0]) and !class_exists($class_name))
	{
		require_once($file[0]); 
		if(method_exists($class_name, 'init')) 
			call_user_func(array($class_name, 'init')); 
		return true;
	}
}
spl_autoload_register('__class_loader');
session::start();
content::start();

/*
########################################
############# ENVIRONNEMENT ############
########################################
*/

// Configuration du site
if(!defined('LOCAL')) define('LOCAL', (in_array(server::get('http_host'), array('localhost:8888', '127.0.0.1'))));
config::set(config::$defaults);
if(!file_exists(PATH_CONF)) f::write(PATH_CONF, '<?'.PHP_EOL.'?>');
else config::load(PATH_CONF);

// Paramètres local/production
config::set(array(
	'minify' =>    !LOCAL,
	'cache' =>     !LOCAL,
	'rewriting' => !LOCAL,
	'local' =>      LOCAL));
		
// Constantes
if(!defined('REWRITING'))     define('REWRITING',   	config::get('rewriting'));
if(!defined('MULTILANGUE'))   define('MULTILANGUE', 	config::get('multilangue'));
if(!defined('CACHE'))
{
	if(LOCAL or PATH_MAIN != NULL)  define('CACHE', false);
	else                            define('CACHE', config::get('cache'));
}

// Affichage et gestion des erreurs
error_reporting(E_ALL | E_STRICT ^ E_DEPRECATED);
set_error_handler('errorHandle');

// Gestion des ressources et chemins
new dispatch();

/*
########################################
############# CONNEXION SQL ############
########################################
*/

// Connexion à la base de données
if(config::get('local.name'))
{
	if(LOCAL) config::set(array(
		'db.host' => 		config::get('local.host'),
		'db.user' => 		config::get('local.user'),
		'db.password' => 	config::get('local.password'),
		'db.name' => 		config::get('local.name')));
	if(!db::connect()) exit('Impossible d\'établir une connexion à la base de données');
}
if(!defined('SQL')) define('SQL', db::connection(), FALSE);

// Mise à jour du moteur
new update();

/*
########################################
############# STATISTIQUES #############
########################################
*/

$ip = server::ip();
if(SQL and config::get('logs'))
{
	if(db::is_table('cerberus_logs'))
	{
		if(!db::field('cerberus_logs', 'ip', array('ip' => $ip)) and ($ip))
		{
			$ua = browser::detect();
			$domaine = url::domain();
			$mobile = (browser::mobile() or browser::ios()) ? 1 : 0;
			
			if(!empty($ua['name']) and !empty($ua['platform']))
				db::insert('cerberus_logs', array(
					'ip'       =>  $ip,
					'date'     =>  'NOW()',
					'platform' => 	$ua['platform'],
					'browser'  => 	$ua['name'],
					'version'  => 	$ua['version'],
					'engine'   => 	$ua['engine'],
					'mobile'   => 	$mobile,
					'locale'   =>  l::locale(),
					'domaine'  => 	$domaine));
		}
	}
	else update::table('cerberus_logs');
}

/*
########################################
########### EN-TÊTE DU SITE ############
########################################
*/

$manifest = (CACHE and file_exists('cache.manifest') and config::get('cache.manifest')) ? 'manifest="cache.manifest"' : NULL;

// Ajout des balises HTML averc leur selecteur correct
echo '<!DOCTYPE html>'.PHP_EOL;
echo '<html ' .$manifest. ' class="' .browser::css(). '">'.PHP_EOL;
content::start();

// Fichiers manquants
if(config::get('bootstrap') and LOCAL)
{
	$required = array(
		dispatch::path(PATH_CERBERUS. '{sass}/base/_custom.sass') => '@import ../../../../' .PATH_COMMON. 'sass/custom'
		);
	foreach($required as $f => $content) if(!file_exists($f)) f::write($f, $content);
}
if(!file_exists(PATH_CACHE)) dir::make(PATH_CACHE);

// Gestion des langues et de la navigation
new l();
new navigation();

/*
########################################
####### DEBUG ET CONSTANTES ############
########################################
*/

// Affichage des superglobales pour debug
if(isset($_GET['cerberus_debug']))
{
	$constantes = get_defined_constants(true);
	$constantes = a::get($constantes, 'user');
	
	$debug  = "[<strong>URL</strong>] " .url::current().'<br/>'.PHP_EOL;
	$debug .= "[<strong>PAGE</strong>] " .navigation::current().'<br/>'.PHP_EOL;
	$debug .= "[<strong>LANGUE</strong>] " .l::current().'<br/>'.PHP_EOL;
	if($_GET) $debug .= "[<strong>GET</strong>]\n\n<pre>" .print_r($_GET, true). '</pre>'.PHP_EOL;
	if($_POST) $debug .= "[<strong>POST</strong>]\n\n<pre>" .print_r($_POST,true). '</pre>'.PHP_EOL;
	if($_SESSION) $debug .= "[<strong>SESSION</strong>]\n\n<pre>" .print_r($_SESSION, true). '</pre>';
	if($constantes) $debug .= "[<strong>CONSTANTES</strong>]\n\n<pre>" .print_r($constantes, true). '</pre>';
	
	echo LOCAL
		? '<div class="cerberus_debug">' .$debug. '</div>'
		: '<p style="display:none">' .str::unhtml($debug). '</p>';
}

/*
########################################
############# MISE EN CACHE ############
########################################
*/

if(CACHE)
{
	// Paramètres préexistants
	if(!isset($setCache)) $setCache = array();
	$setCache['name'] = a::get($setCache, 'name', navigation::current());
	$setCache['cache_time'] = a::get($setCache, 'cache_time');
	$setCache['cache_get_variables'] = a::get($setCache, 'cache_get_variables', true);
	$setCache['get_remove'] = a::get($setCache, 'get_remove', array('page', 'pageSub', 'PHPSESSID', 'langue', 'gclid', 'cerberus_debug'));
	$setCache['type'] = 'html';
	
	// Autoriser le caching
	if(navigation::$page == 'admin') $caching = FALSE;
	elseif(SQL and db::is_table('cerberus_structure'))
		$caching = db::field('cerberus_structure', 'cache', db::where(array('CONCAT_WS("-",parent,page)' => $setCache['name'], 'parent' => $setCache['name']), 'OR'));
	if(!isset($caching)) $caching = TRUE;
	if(isset($setCache['cache'])) $caching = $setCache['cache'];
	
	// Démarrage de la mise en cache
	if($caching) $start = cache::page($setCache['name'], $setCache);
}

// Chargement des modules Cerberus
$cerberus = new Cerberus(config::get('cerberus'));
if(db::connection() and CACHE and function_exists('backupSQL')) backupSQL();
?>