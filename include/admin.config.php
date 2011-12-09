<?php
$config_file = 'cerberus/conf.php';
$arrays = array('cerberus', 'langues');
$bool = array('rewriting', 'cache', 'logs', 'multilangue');

// Enregistrement des paramètres
asort($_POST);
if(isset($_POST['db-host']))
{
	$getconf = f::read($config_file);

	foreach($_POST as $key => $value)
	{
		// Définition du type de valeur
		$key = str_replace('-', '.', $key);
		if(in_array($key, $arrays))
		{
			if(str::find(',', $value)) $formatted_value = implode("', '", explode(',', $value));
			$formatted_value = 'array(\'' .$formatted_value. '\')';
		}
		elseif(in_array($value, array('TRUE','FALSE'))) $formatted_value = $value;
		else $formatted_value = "'" .$value. "'";
		
		// Recherche de sa présence dans le fichier config
		if(preg_match('#\$config\[\'(' .$key. ')\'\] = (.+);#', $getconf))
		{
			$getconf = preg_replace(
				'#\$config\[\'(' .$key. ')\'\] = (.+);#',
				'$config[\'$1\'] = ' .$formatted_value. ';',
				$getconf);
		}
		else if(!empty($value)) $getconf = str_replace('?>', '$config[\'' .$key. '\'] = ' .$formatted_value. ";\n?>", $getconf); 
	}
	
	f::write($config_file, $getconf);
	prompt('La configuration du site a été changée avec succès');
}

// Paramètres de Cerberus
$CONFIGURATION = 
array(
	'Paramètres du site' => array(
		'rewriting' => 'Activer la réecriture d\'URL',
		'cache' => 'Mise en cache',
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
		'http' => 'Adresse externe du site',
		'mail' => 'Adresse de contact')
);

if(file_exists($config_file)) include($config_file);
if(!isset($config)) $config = array();
	
// Création du formulaire
$form = new form(false);
$select = new select();
foreach($CONFIGURATION as $FIELDSET => $FIELDS)
{
	$form->openFieldset($FIELDSET);
		foreach($FIELDS as $FIELD => $TRADUCTION)
		{
			$value = a::get($config, $FIELD, FALSE);
			if(in_array($FIELD, $bool))
			{
				$value = str::boolprint($value);
				$select->newSelect($FIELD, $TRADUCTION);
				$select->appendList(array('TRUE' => 'Oui', 'FALSE' => 'Non'), false);
				$select->setValue($value);
				$form->insertText($select);
			}
			else
			{
				if(is_array($value)) $value = implode(',', $value);
				$form->addText($FIELD, $TRADUCTION, $value);
			}
		}
	$form->closeFieldset();
}
$form->addSubmit('Enregistrer');
echo $form;
?>