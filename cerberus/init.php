<?php
// Gestion des erreurs
include('tools/errorHandle.php');
$config_file = 'cerberus/conf.php';
date_default_timezone_set('Europe/Paris');
ini_set('error_log', 'cerberus/cache/error.log');
ini_set('log_errors', 'On');

// Chargement du moteur Cerberus
foreach(glob('cerberus/class/{kirby.*.php,core.*.php}', GLOB_BRACE) as $file) require_once($file);
require_once('cerberus/class/class.navigation.php');
require_once('cerberus/tools/display.php');
$REVISION = 353;
s::start();

/*
########################################
############# ENVIRONNEMENT ############
########################################
*/
// Configuration du site
timer::save().timer::start('config');
if(!file_exists($config_file)) f::write($config_file, NULL);
else config::load($config_file);
config::set('local', (in_array(server::get('http_host'), array('localhost:8888', '127.0.0.1'))));

if(config::get('local'))
{
	config::set(array(
		'cache' => false,
		'rewriting' => false,
		'db.debug' => true));
}

// Constantes
define('SQL', config::get('local.name', FALSE));
define('REWRITING', config::get('rewriting', FALSE));
define('LOCAL', config::get('local', FALSE));
define('MULTILANGUE', config::get('multilangue', FALSE));
if(LOCAL)	define('CACHE', FALSE);
else		define('CACHE', config::get('cache', TRUE));
// Affichage et gestion des erreurs
error_reporting(E_ALL | E_STRICT ^ E_DEPRECATED);
set_error_handler('errorHandle');

/*
########################################
############# CONNEXION SQL ############
########################################
*/

// Connexion à la base de données
timer::save('config');
timer::start('sql');
if(SQL)
{
	if(LOCAL) config::set(array(
		'db.host' => config::get('local.host'),
		'db.user' => config::get('local.user'),
		'db.password' => config::get('local.password'),
		'db.name' => config::get('local.name')));
	if(!db::connect()) exit('Impossible d\'établir une connexion à la base de données');
}

// Mise à jour du moteur
new update($REVISION);

/*
########################################
############# STATISTIQUES #############
########################################
*/

timer::save('sql').timer::start('logs');
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
			
			if(!empty($ua['browser']) and !empty($ua['platform']))
				db::insert('cerberus_logs', array(
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
	else update::table('cerberus_logs');
}
$userAgent = browser::css();

// Ajout des balises HTML averc leur selecteur correct
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'.PHP_EOL;
echo '<html xmlns="http://www.w3.org/1999/xhtml" class="' .$userAgent. '">'.PHP_EOL;

/*
########################################
##### PARAMETRES CURRENT DU SITE #######
########################################
*/

// Gestion des langues
timer::save('logs').timer::start('langue');
if(MULTILANGUE and SQL) $index = new l();

// Gestion de la navigation
timer::save('langue').timer::start('navigation');
$desired = new navigation();

// Affichage des superglobales pour debug
if(isset($_GET['cerberus_debug']))
{
	$debug  = "[<strong>URL</strong>] " .url::current().PHP_EOL;
	$debug .= "[<strong>PAGE</strong>] " .$desired->current().PHP_EOL;
	$debug .= "[<strong>LANGUE</strong>] " .l::current().PHP_EOL;
	if($_GET) $debug .= "[<strong>GET</strong>]\n\n<div>" .print_r($_GET, true). '</div>'.PHP_EOL;
	if($_POST) $debug .= "[<strong>POST</strong>]\n\n<div>" .print_r($_POST,true). '</div>'.PHP_EOL;
	if($_SESSION) $debug .= "[<strong>SESSION</strong>]\n\n<div>" .print_r($_SESSION, true). '</div>';
	
	echo LOCAL
		? '<div class="cerberus_debug">' .nl2br($debug). '</div>'
		: '<p style="display:none">' .$debug. '</p>';
}

/*
########################################
############# MISE EN CACHE ############
########################################
*/

timer::save('navigation').timer::start('cerberus');
if(CACHE)
{
	$start = content::cache_start($desired->current());
	if(!$start)
	{
		content::cache_end();
		exit();
	}
}

// Chargement des modules Cerberus
$cerberus = new Cerberus(config::get('cerberus'));
$dispatch = new dispatch();
if(db::connection() and CACHE) backupSQL();

/*
########################################
################# GLOBALES #############
########################################
*/

// Génération du fichier META
timer::save('cerberus').timer::start('meta');
$cerberus->meta();
timer::save('meta').timer::start('end');
?>