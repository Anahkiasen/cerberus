<?php
// Chargement du moteur
foreach(glob('cerberus/core/*.php') as $file) require_once($file);
include('tools/beArray.php');
include('tools/errorHandle.php');

s::start();

// Configuration du site
config::load('cerberus/conf.php');
config::set('local', (in_array($_SERVER['HTTP_HOST'], array('localhost:8888', '127.0.0.1'))));
l::load('cerberus/cache/lang-{langue}.php');

if(config::get('local'))
{
	config::set(array(
		'production' => false,
		'rewriting' => false,
		'db.debug' => true));
}

define('REWRITING', config::get('rewriting'));
define('PRODUCTION', config::get('production'));
define('LOCAL', config::get('local'));
define('MULTILANGUE', config::get('multilangue'));

// Affichage et gestion des erreurs
error_reporting(E_ALL|E_STRICT);
set_error_handler('errorHandle');

// Connexion à la base de données
if(LOCAL) config::set(array(
	'db.host' => config::get('local.host'),
	'db.user' => config::get('local.user'),
	'db.mdp' => config::get('local.mdp'),
	'db.name' => config::get('local.name')));
$db = new db();
$connected = $db->connect();

// Chargement des modules Cerberus
$cerberus = new Cerberus(config::get('cerberus'));

// Sauvegarde de la base
if($db->connection()) backupSQL();

// Fichier multilingue
if(MULTILANGUE)
{
	$index = new l();
	$index = l::get();
}
?>