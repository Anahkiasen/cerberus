<?php
use Cerberus\Admin\Admin,
    Cerberus\Core\Config,
    Cerberus\Modules\Form,
    Cerberus\Toolkit\Arrays as a,
    Cerberus\Toolkit\String as str,
    Cerberus\Toolkit\Url;

$arrays = array('cerberus', 'langues');
$bool = array('rewriting', 'minify', 'bootstrap', 'multi_admin', 'cache', 'logs', 'multilangue');

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
			if(str::find(',', $value)) $formattedValue = explode(',', $value);
			else $formattedValue = array($value);
		}
		elseif(in_array($value, array('true','false'))) $formattedValue = $value;
		else $formattedValue = "'" .$value. "'";

		config::hardcode($key, $formattedValue);
	}

	str::display(l::get('admin.config.ok'), 'success');
}

// Paramètres de Cerberus
$configuration =
array(
	'Paramètres du site' => array(
		'rewriting' => 'Activer la réecriture d\'URL',
		'cache' => 'Mise en cache',
		'cachetime' => 'Durée du cache par défaut'),
	'Administration' => array(
		'admin.login' => 'Administrateur par défaut',
		'admin.password' => 'Mot de passe par défaut',
		'multi_admin' => 'Multiples administrateurs'),
	'Modules' => array(
		'minify' => 'Minifier les ressources CSS et JS',
		'bootstrap' => 'Utiliser le framework Bootstrap',
		'logs' => 'Activer les statistiques'),
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

// Création du formulaire
$forms = new Form();
foreach($configuration as $fieldset => $fields)
{
	$forms->openFieldset($fieldset);
		foreach($fields as $field => $traduction)
		{
			$value = config::get($field, false);
			if(in_array($field, $bool))
			{
				$value = str::boolprint($value);
				$forms->addSelect($field, $traduction, array('true' => 'Oui', 'false' => 'Non'), $value);
			}
			else
			{
				if(is_array($value)) $value = implode(',', $value);
				$forms->addText($field, $traduction, $value);
			}
		}
	$forms->closeFieldset();
}
$forms->addSubmit('Enregistrer');
$forms->render();
