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
function createIndex($arrayLang = array('en', 'fr'), $database = 'langue')
{
	$index = array();
	$filename = 'cerberus/cache/index-' .$database. '.php';
	
	$tables = mysqlQuery('SELECT tag FROM langue LIMIT 1');
	if(!empty($tables))
	{
		if(!PRODUCTION) sunlink($filename); // Suppression de la version existante
		if(file_exists($filename) and PRODUCTION) include_once($filename);
		else
		{
			// Récupération de la base de langues
			$thisIndex = mysqlQuery('SELECT tag, ' .implode(', ', $arrayLang). ' FROM ' .$database. ' ORDER BY tag ASC', true, 'tag');
			if($thisIndex)
			{
				// Création de l'index
				foreach($thisIndex as $tag => $traduction)
				{
					foreach($arrayLang as $langue)
						$index[$langue][$tag] = $traduction[$langue];
				}
				
				// Ecriture du fichier PHP
				$renderPHP = "<?php \n";
				foreach($arrayLang as $cle)
				{
					if(isset($index[$cle]))
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
}
function indexCSV($database = 'langue')
{
	$rows = array_keys(mysqlQuery('DESCRIBE ' .$database));
	$values = mysqlQuery('SELECT * FROM ' .$database. ' ORDER BY tag ASC', TRUE, 'tag');
	
	$renderCSV = implode("\t", $rows). "\t\n";
	foreach($values as $key => $value) $renderCSV .= $key. "\t" .implode("\t", $value). "\t\n";
	
	foreach (glob('pages/text/*.html') as $filepath)
	{
		$filename = basename($filepath);
		$filename = substr($filename, 0, (strlen ($filename)) - (strlen (strrchr($filename, '.'))));
		
		$langue = substr($filename, 0, 2);
		$filename = str_replace($langue. '-', '', $filename);
	
		$htmlArray[$filename][$langue] = htmlentities(file_get_contents($filepath));
	}
	
	//print_r(array_merge($values, $htmlArray));
	//foreach($htmlArray as $key => $value) $renderCSV .= $key. "\t" .htmlentities(implode("\t", $value)). "\t\n";
	
	sfputs($database. '.csv', $renderCSV);
}
// Fonctions index
function indexDisplay($string, $langue = NULL)
{
	echo display(index($string, $langue));
}
function index($string, $langue = NULL)
{
	global $index;
	
	if(isset($langue)) $langueIndex = $langue;
	elseif(!isset($langue) and isset($_SESSION['langueSite'])) $langueIndex = $_SESSION['langueSite'];
	elseif(!isset($langue, $_SESSION['langueSite'])) $langueIndex = 'fr';
		
	if(isset($index[$langueIndex][$string]) and !empty($index[$langueIndex][$string])) return $index[$langueIndex][$string];
	else return '<span style="color:red">[' .$string. '(' .$langueIndex. ')]</span>';
}
function indexHTML($path, $langue = NULL)
{
	global $index;
	
	$langueIndex = (empty($langue))
		? $_SESSION['langueSite']
		: $langue;
		
	$thisFile = 'pages/text/' .$langueIndex. '-' .$path. '.html';
	if(file_exists($thisFile)) include($thisFile);
	else echo '<span style="color:red">[' .$path. '(' .$langueIndex. ')]</span>';
}
?>