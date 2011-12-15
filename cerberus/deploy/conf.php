<?php
// Environnement
$config['production'] = false;
$config['rewriting'] = false;

// Cerberus
$config['cerberus'] = array('pack.sql', 'pack.navigation');

// Langues
$config['multilangue'] = true;
$config['langue_default'] = 'fr';
$config['langues'] = array('fr');

// Identifiants SQL
$config['local.host'] = 'localhost';
$config['local.user'] = 'root';
$config['local.password'] = 'root';
$config['local.name'] = '';

$config['db.host'] = '';
$config['db.user'] = '';
$config['db.password'] = '';
$config['db.name'] = '';

// Variables
$config['http'] = '';
$config['mail'] = '';
?>