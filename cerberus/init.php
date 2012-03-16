<?php
// Gestion des erreurs
include('tools/errorHandle.php');
$config_file = 'cerberus/conf.php';
header('Content-type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Paris');
ini_set('error_log', 'cerberus/cache/error.log');
ini_set('log_errors', 'On');

// Chargement du moteur Cerberus
include('cerberus/class/kirby.request.php');
function __class_loader($class_name) 
{
	$class_name = str_replace('_', '.', strtolower($class_name));
	$file = glob('cerberus/class/{kirby,class,core,kirby.plugins}.' .$class_name. '*.php', GLOB_BRACE);
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

/*
########################################
############# ENVIRONNEMENT ############
########################################
*/

// Configuration du site
if(!file_exists($config_file)) f::write($config_file, NULL);
else config::load($config_file);
config::set('local', (in_array(server::get('http_host'), array('localhost:8888', '127.0.0.1'))));

// Paramètres si LOCAL
if(config::get('local'))
{
	config::set(array(
		'cache' =>     false,
		'rewriting' => false,
		'db.debug' =>  true));
}

// Constantes
if(!defined('REWRITING'))     define('REWRITING',   	config::get('rewriting',   FALSE));
if(!defined('LOCAL'))         define('LOCAL', 	   	config::get('local', 	  	FALSE));
if(!defined('MULTILANGUE'))   define('MULTILANGUE', 	config::get('multilangue', FALSE));
if(!defined('CACHE'))
{
	if(LOCAL)   define('CACHE', false);
	else        define('CACHE', config::get('cache', TRUE));
}

// Affichage et gestion des erreurs
error_reporting(E_ALL | E_STRICT ^ E_DEPRECATED);
set_error_handler('errorHandle');

// Gestion des ressources et chemins
$dispatch = new dispatch();

/*
########################################
############# CONNEXION SQL ############
########################################
*/

// Connexion à la base de données
if(config::get('local.name',  FALSE))
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

$ip = server::get('remote_addr');
if(SQL)
{
	if(config::get('logs', FALSE)) if(db::is_table('cerberus_logs'))
	{
		if(!db::field('cerberus_logs', 'ip', array('ip' => $ip)) and ($ip))
		{
			$ua = browser::detect();
			$domaine = url::domain();
			$mobile = (browser::mobile() or browser::ios()) ? 1 : 0;
			if(!empty($ua['name']) and !empty($ua['platform']))
				db::insert('cerberus_logs', array(
					'ip' => 		$ip,
					'date' => 		'NOW()',
					'platform' => 	$ua['platform'],
					'browser' => 	$ua['name'],
					'version' => 	$ua['version'],
					'engine' => 	$ua['engine'],
					'mobile' => 	$mobile,
					'domaine' => 	$domaine));
		}
	}
	else update::table('cerberus_logs');
}

/*
########################################
########### EN-TÊTE DU SITE ############
########################################
*/

// Ajout des balises HTML averc leur selecteur correct
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'.PHP_EOL;
echo '<html xmlns="http://www.w3.org/1999/xhtml" class="' .browser::css(). '">'.PHP_EOL;

// Fichiers manquants
if(config::get('boostrap', true) and LOCAL)
{
	$required = array(
		dispatch::path(PATH_CERBERUS. '{sass}/_custom.sass'));
	foreach($required as $f) if(!file_exists($f)) f::write($f);
}
if(!file_exists('ceberus/cache/')) dir::make('cerberus/cache/');

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
	$setCache['cache_variables'] = a::get($setCache, 'cache_variables', true);
	$setCache['cache_get_variables'] = a::get($setCache, 'cache_get_variables', true);
	$setCache['get_remove'] = a::get($setCache, 'get_remove', array('page', 'pageSub', 'PHPSESSID', 'langue', 'gclid', 'cerberus_debug'));
	$setCache['type'] = 'html';
	
	// Autoriser le caching
	if(navigation::$page == 'admin') $caching = FALSE;
	elseif(SQL and db::is_table('cerberus_structure'))
		$caching = db::field('cerberus_structure', 'cache', db::where(array('CONCAT_WS("-",parent,page)' => $setCache['name'], 'parent' => $setCache['name']), 'OR'));
	else $caching = TRUE;
	$setCache['caching'] = a::get($setCache, 'caching', $caching);
	
	// Démarrage de la mise en cache
	$start = cache::page($setCache['name'], $setCache);
}

// Chargement des modules Cerberus
$cerberus = new Cerberus(config::get('cerberus'));
if(db::connection() and CACHE and function_exists('backupSQL')) backupSQL();

/*
########################################
############ BALISES META ##############
########################################
*/

if(update::revision() < 478) meta::head();

// Balise base
if(REWRITING)
{
	$baseref = LOCAL ? config::get('base.local') : config::get('base.online');
	echo '<base href="' .config::get('http').$baseref. '" />';
}
?>