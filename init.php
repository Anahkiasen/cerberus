<?php
// Chargement du moteur
include('tools/errorHandle.php');
date_default_timezone_set('Europe/Paris');
ini_set('error_log', 'cerberus/cache/error.log');
ini_set('log_errors', 'On');

foreach(glob('cerberus/class/kirby.*.php') as $file) require_once($file);
require_once('cerberus/class/core.cerberus.php');
require_once('cerberus/class/class.navigation.php');
s::start();

/*
########################################
############# ENVIRONNEMENT ############
########################################
*/

// Configuration du site
config::load('cerberus/conf.php');
config::set('local', (in_array(server::get('http_host'), array('localhost:8888', '127.0.0.1'))));

if(config::get('local'))
{
	config::set(array(
		'production' => false,
		'rewriting' => false,
		'db.debug' => true));
}

// Constantes
define('REWRITING', config::get('rewriting', FALSE));
define('PRODUCTION', config::get('production', FALSE));
define('LOCAL', config::get('local', FALSE));
define('MULTILANGUE', config::get('multilangues', TRUE));

// Affichage et gestion des erreurs
error_reporting(E_ALL | E_STRICT ^ E_DEPRECATED);
set_error_handler('errorHandle');

/*
########################################
############# CONNEXION SQL ############
########################################
*/

// Connexion à la base de données
if(LOCAL) config::set(array(
	'db.host' => config::get('local.host'),
	'db.user' => config::get('local.user'),
	'db.password' => config::get('local.password'),
	'db.name' => config::get('local.name')));
	if(!db::connect()) exit('Impossible d\'établir une connexion à la base de données');

/*
########################################
############# STATISTIQUES #############
########################################
*/

$ip = server::get('remote_addr');
if(db::is_table('logs'))
{
	if(!db::row('logs', 'ip', array('ip' => $ip)) and ($ip))
	{
		$ua = browser::detect();
		$domaine = a::get(explode('/', url::short()), 0);
		$mobile = (browser::mobile() or browser::ios()) ? 1 : 0;
		
		if(!empty($ua['browser']) and !empty($ua['platform']))
			db::insert('logs', array(
				'ip' => $ip,
				'date' => 'NOW()',
				'platform' => $ua['platform'],
				'browser' => $ua['browser'],
				'version' => $ua['version'],
				'engine' => $ua['engine'],
				'mobile' => $mobile,
				'domaine' => $domaine));
	}
}
$userAgent = browser::css();

// Ajout des balises HTML averc leur selecteur correct
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml" class="' .$userAgent. '">';

/*
########################################
############# MISE EN CACHE ############
########################################
*/

// Affichage des superglobales pour debug
if(isset($_GET['debug']))
{
	$debug  = "[<strong>URL</strong>] " .url::current().PHP_EOL;
	$debug .= "[<strong>LANGUE</strong>] " .l::current().PHP_EOL;
	$debug .= "[<strong>GET</strong>]\n" .print_r($_GET, true).PHP_EOL;
	$debug .= "[<strong>POST</strong>]\n" .print_r($_POST,true).PHP_EOL;
	$debug .= "[<strong>SESSION</strong>]\n" .print_r($_SESSION, true).PHP_EOL;
	
	echo LOCAL ? nl2br($debug) : '<p style="display:none">' .$debug. '</p>';
}

$desired = new navigation();
$start = content::cache_start($desired->current());
if(!$start)
{
	content::cache_end();
	exit();
}

// Chargement des modules Cerberus
$cerberus = new Cerberus(config::get('cerberus'));
if(db::connection()) backupSQL();

/*
########################################
################# GLOBALES #############
########################################
*/

// Fichier multilingue
if(MULTILANGUE)
{
	$index = new l();
	$index->load('cerberus/cache/lang-{langue}.php');
	$index = l::get();
}

// Génération du fichier META
$cerberus->meta();
?>