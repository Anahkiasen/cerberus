<?php
function indexToPhp($arrayLang = array('en', 'fr', 'de'), $base = 'langue', $overwrite = false)
{
	$nomDuFichier = $base. '.php';
	if($overwrite == true && file_exists($nomDuFichier)) unlink($nomDuFichier); // Suppression de la version existante
	
	// Si fichier langue existe déjà
	if(file_exists($nomDuFichier)) include($nomDuFichier);
	else
	{
		// Récupération de la base de langues
		$php = $csv = '';
		foreach($arrayLang as $value) $tag = (isset($tag)) ? $tag. ', ' .$value : $value;
		$index0 = mysql_query('SELECT tag, ' .$tag. ' FROM ' .$base. ' ORDER BY tag ASC');
		if(mysql_num_rows($index0) != 0)
		{
			// Création de l'index
			while($row = mysql_fetch_assoc($index0))
			{
				foreach ($row as $fieldname => $fieldvalue) 
				{
					if($fieldname == 'tag') $tag = $fieldvalue;
					else $index[$fieldname][$tag] = $fieldvalue;
				}
			}
	
			$php = '<?php';
			foreach($arrayLang as $cle)
			{
				foreach($index[$cle] as $key => $value)
				{
					// Création du fichier CSV
					if($csv != '') $csv .= '#
';
					$csv .= $cle. '#' .$key. '#' .$value;
				
					// Création du fichier PHP
					if($php != '') $php .= '
';
					$php .= '$index[\'' .$cle. '\'][\'' .$key. '\'] = \'' .addslashes($value). '\';';
				}
			}
			$php .= '?>';
		}
		
		sfputs($nomDuFichier, $php);
		sfputs($base. '.csv', $php);
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
	$langueIndex = ($langue == '') ? $_SESSION['langueSite'] : $langue;
	if(isset($index[$langueIndex][$string]) && !empty($index[$langueIndex][$string])) return $index[$langueIndex][$string];
	else return '<span style="color:red">[TERME MANQUANT]</span>';
}
?>