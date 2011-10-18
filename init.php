<?php
// Chargement du moteur
include('tools/errorHandle.php');
foreach(glob('cerberus/core/*.php') as $file) require_once($file);
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
define('REWRITING', config::get('rewriting'));
define('PRODUCTION', config::get('production'));
define('LOCAL', config::get('local'));
define('MULTILANGUE', config::get('multilangue'));

// Affichage et gestion des erreurs
error_reporting(E_ALL|E_STRICT|E_DEPRECATED);
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
	'db.mdp' => config::get('local.mdp'),
	'db.name' => config::get('local.name')));

// Chargement des modules Cerberus
$cerberus = new Cerberus(config::get('cerberus'));

// Sauvegarde de la base
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
	l::load('cerberus/cache/lang-{langue}.php');
	$index = l::get();
}

// Génération du fichier META
$cerberus->meta();

/*
########################################
############# STATISTIQUES #############
########################################
*/

$ip = server::get('remote_addr');

if(!db::row('logs', 'ip', array('ip' => $ip)) and ($ip))
{
	$ua = brower::detect();
	$mobile = (browser::mobile() or browser::ios()) ? 1 : 0;
	if(!empty($ua['browser']) and !empty($ua['platform']))
		db::insert('logs', array(
			'ip' => $ip,
			'date' => 'NOW()',
			'platform' => $ua['platform'],
			'browser' => $ua['browser'],
			'version' => $ua['version'],
			'engine' => $ua['engine'],
			'mobile' => $mobile));
}

$userAgent = browser::css();
?>