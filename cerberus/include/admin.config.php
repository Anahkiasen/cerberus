<?php
$config_file = 'cerberus/conf.php';
$arrays = array('cerberus', 'langues');
$bool = array('rewriting', 'multi_admin', 'cache', 'logs', 'multilangue');

// Enregistrement des paramètres
asort($_POST);
if(isset($_POST['db-host']))
{
	foreach($_POST as $key => $value)
	{
		// Définition du type de valeur
		$key = str_replace('-', '.', $key);
		if(in_array($key, $arrays))
		{
			if(str::find(',', $value)) $formatted_value = explode(',', $value);
			else $formatted_value = array($value);
		}
		elseif(in_array($value, array('TRUE','FALSE'))) $formatted_value = $value;
		else $formatted_value = "'" .$value. "'";
		
		config::hardcode($key, $formatted_value);
	}
	
	str::display(l::get('admin.config.ok'), 'success');
}

// Paramètres de Cerberus
$CONFIGURATION = 
array(
	'Paramètres du site' => array(
		'rewriting' => 'Activer la réecriture d\'URL',
		'cache' => 'Mise en cache',
		'cachetime' => 'Durée du cache par défaut',
		'multi_admin' => 'Multiples administrateurs',
		'logs' => 'Statistiques'),
	'International' => array(
		'multilangue' => 'Site multilingue',
		'langue_default' => 'Langue par défaut',
		'langues' => 'Langues du site'),
	'Paramètres SQL' => array(
		'local.host' => 'Serveur local',
		'local.user' => 'Identifiant local',
		'local.password' => 'Mot de passe local',
		'local.name' => 'Base de donnée locale',	
		'db.host' => 'Serveur en ligne',
		'db.user' => 'Utilisateur en ligne',
		'db.password' => 'Mot de passe en ligne',
		'db.name' => 'Base de données en ligne'),
	'Informations' => array(
		'sitename' => 'Nom du site',
		'http' => 'Adresse externe du site',
		'mail' => 'Adresse de contact'),
	'Hébergement' => array(
		'base.local' => 'Sous-dossier local (facultatif)',
		'base.online' => 'Sous-dossier en ligne (facultatif)')
);

if(file_exists($config_file)) include($config_file);
if(!isset($config)) $config = array();
	
// Création du formulaire
$forms = new forms();
foreach($CONFIGURATION as $FIELDSET => $FIELDS)
{
	$forms->openFieldset($FIELDSET);
		foreach($FIELDS as $FIELD => $TRADUCTION)
		{
			$value = a::get($config, $FIELD, FALSE);
			if(in_array($FIELD, $bool))
			{
				$value = str::boolprint($value);
				$forms->addSelect($FIELD, $TRADUCTION, array('TRUE' => 'Oui', 'FALSE' => 'Non'), $value);
			}
			else
			{
				if(is_array($value)) $value = implode(',', $value);
				$forms->addText($FIELD, $TRADUCTION, $value);
			}
		}
	$forms->closeFieldset();
}
$forms->addSubmit('Enregistrer');
$forms->render();
?>