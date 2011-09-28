<?php
/*
	Fonction findString
	# Trouve un/des terme(s) dans une/des phrase(s)
	
	$needle
		Terme(s) recherchée(s)
	$haystack
		Phrase(s) dans laquelle/lesquelles rechercher
	$exclusive
		TRUE	Pour que la fonction renvoit true, toutes les $needle devront avoir été trouvées
				dans $haystack [ou] la $needle devra avoir été trouvée dans tous les $haystack.
		FALSE	Renverra true dès que la fonction trouvera n'importe quelle $needle dans n'importe
				quelle $haystack.
*/
function findString($needle, $haystack, $exclusive = true)
{
	// Si nous avons plusieurs termes/phrases
	if(is_array($needle) or is_array($haystack))
	{
		$result = 0;
		if(is_array($needle))
		{
			$numberEntry = count($needle);
			foreach($needle as $value)
			{
				$pos = strpos($haystack, $value);
				if($pos !== false) $result++;
			}
		}
		elseif(is_array($haystack))
		{
			$numberEntry = count($haystack);
			foreach($haystack as $value)
			{
				$pos = strpos($value, $needle);
				if($pos !== false) $result++;
			}
		}
		
		// Résultat selon le mode exclusif
		if($exclusive and $result == $numberEntry) return true;
		elseif(!$exclusive and $result != 0) return true;
		else return false;
	}
	else
	{
		// Simple strpos
		$pos = strpos($haystack, $needle);
		if($pos === false) return FALSE;
		else return TRUE;
	}
}
?>