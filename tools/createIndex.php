<?php
/*
	Fonction createIndex
	# Créer un index multilangue
	
	@ Dépendances
	@ sfputs
	
	$arrayLang
		Array content les langues dans lesquelles créer l'index
	$resetMode
		Active ou non la réecriture de l'index
	$database
		Nom de la base contenant les traductions au format [tag,langue1,langue2,etc.] et
		à partir de laquelle créer l'index
	------------------------------------------------------------------------------------
	Fonction indexCSV
	# Créer un fichier .csv contenant la totalité de l'index des langues
	
	$database
		Nom de la base contenant les traductions au format [tag,langue1,langue2,etc.] et
		à partir de laquelle créer l'index
	------------------------------------------------------------------------------------
	Fonction index
	# Affiche la traduction correcte d'un terme donné selon la langue choisie
	
	$string
		Mot-clé appelant la traduction correspondante
	$langue
		Langue dans laquelle afficher le terme - par défaut celle choisie par le visiteur
*/
function createIndex($arrayLang = array('en', 'fr'), $resetMode = TRUE, $database = 'langue')
{
	$index = array();
	$filename = $database. '.php';
	
	if(file_exists($filename) and $resetMode == TRUE) unlink($filename); // Suppression de la version existante
	if(file_exists($filename) and $resetMode == FALSE) include_once($filename);
	else
	{
		// Récupération de la base de langues
		$thisIndex = mysql_query('SELECT tag, ' .implode(', ', $arrayLang). ' FROM ' .$database. ' ORDER BY tag ASC');
		if(mysql_num_rows($thisIndex) != 0)
		{
			// Création de l'index
			while($row = mysql_fetch_assoc($thisIndex))
			{
				foreach ($row as $fieldname => $fieldvalue) 
					if($fieldname != 'tag') $index[$fieldname][$row['tag']] = $fieldvalue;
			}

			// Ecriture du fichier PHP
			$renderPHP = "<?php \n";
			foreach($arrayLang as $cle)
			{
				foreach($index[$cle] as $key => $value)
					$renderPHP .= '$index[\'' .$cle. "']['" .$key. "'] = '" .addslashes($value). "';\n";
			}
				
			sfputs($filename, $renderPHP. '?>');
		}
	}
	
	// Langue du site
	if(!isset($_SESSION['langueSite'])) $_SESSION['langueSite'] = 'fr';
	if(isset($_GET['langue']) && in_array($_GET['langue'], $arrayLang)) $_SESSION['langueSite'] = $_GET['langue'];
	
	// Langue de l'administration
	if(!isset($_SESSION['admin']['langue'])) $_SESSION['admin']['langue'] = 'fr';
	if(isset($_GET['adminLangue']) && in_array($_GET['adminLangue'], $arrayLang)) $_SESSION['admin']['langue'] = $_GET['adminLangue'];
	
	return $index;
}
function indexCSV($database = 'langue')
{
	$rows = array_keys(mysqlQuery('DESCRIBE ' .$database));
	$values = mysqlQuery('SELECT * FROM ' .$database. ' ORDER BY tag ASC', TRUE, 'tag');
	
	$renderCSV = implode("\t", $rows). "\t\n";
	foreach($values as $key => $value) $renderCSV .= $key. "\t" .implode("\t", $value). "\t\n";
	
	sfputs($database. '.csv', $renderCSV);
}
function index($string, $langue = '')
{
	global $index;
	
	$langueIndex = ($langue == '')
		? $_SESSION['langueSite']
		: $langue;
		
	if(isset($index[$langueIndex][$string]) && !empty($index[$langueIndex][$string])) return $index[$langueIndex][$string];
	else return '<span style="color:red">[' .$string. '(' .$langueIndex. ')]</span>';
}
?>