<?php
function createIndex($arrayLang = array('en', 'fr'), $database = 'langue', $overwrite = FALSE)
{
	$filename = $database. '.php';
	$renderPHP = "<?php \n";
	
	if(file_exists($filename) and $overwrite == TRUE) unlink($filename); // Suppression de la version existante
	elseif(file_exists($filename) and $overwrite == FALSE) include_once($filename);
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
				{
					if($fieldname == 'tag') $tag = $fieldvalue;
					else $index[$fieldname][$tag] = $fieldvalue;
				}
			}

			// Ecriture du fichier PHP
			foreach($arrayLang as $cle)
				foreach($index[$cle] as $key => $value)
				{
					$renderCSV .= $cle. '#' .$key. '#' .$value. "#\n";
					$renderPHP .= '$index[\'' .$cle. "']['" .$key. "'] = '" .addslashes($value). "';\n";
				}
				
			sfputs($database. '.csv', $renderCSV);
			sfputs($filename, $renderPHP. ' ?>');
		}
	}
	
	if(!isset($index)) $index = array();
	
	// Création des variables
	if(!isset($_SESSION['langueSite'])) $_SESSION['langueSite'] = 'fr';
	if(isset($_GET['langue']) && in_array($_GET['langue'], $arrayLang)) $_SESSION['langueSite'] = $_GET['langue'];
	
	if(!isset($_SESSION['admin']['langue'])) $_SESSION['admin']['langue'] = 'fr';
	if(isset($_GET['adminLangue']) && in_array($_GET['adminLangue'], $arrayLang)) $_SESSION['admin']['langue'] = $_GET['adminLangue'];
	
	return $index;
}
function index($string, $langue = '')
{
	global $index;
	
	$langueIndex = ($langue == '')
		? $_SESSION['langueSite']
		: $langue;
		
	if(isset($index[$langueIndex][$string]) && !empty($index[$langueIndex][$string])) return $index[$langueIndex][$string];
	else return '<span style="color:red">[TERME MANQUANT]</span>';
}
?>