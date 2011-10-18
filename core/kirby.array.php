<?php
class a
{
	// Récupérer une entrée d'un array - retourne $default si inexistant
	static function get($array, $key, $default = NULL)
	{
		return (isset($array[$key])) ? $array[$key] : $default;
	}
	
	// Supprimer une entrée d'un tableau
	function remove($array, $search, $multiple = FALSE)
	{
		if(!$multiple) unset($array[$search]);
		else 
		{
			$found_all = FALSE;
			while(!$found_all)
			{
				$index = array_search($search, $array);
				if($index !== false) unset($array[$index]);
				else $found_all = true;
			}
		}
		return $array;
	}
	
	// Force un élément à être un array
	static function beArray(&$variable)
	{
		if(!isset($variable)) $return = array();
		$return = (!is_array($variable)) ? array($variable) : $variable;
		
		// Retour de la valeur et modification	
		$variable = $return;
		return $return;
	}
	
	// Insérer un élément à la position X
	function inject($array, $position, $element = 'placeholder')
	{
		$start = array_slice($array, 0, $position);
		$end = array_slice($array, $position);
		return array_merge($start, (array)$element, $end);
	}

	// Extraire un champ d'un array multidimensionnel
	function extract($array, $key)
	{
		$output = array();
		foreach($array as $a) if(isset($a[$key])) $output[] = $a[$key];
		return $output;
	}

	// Shuffle un array en conservant les paires key/value
	function shuffle($array)
	{
		$aux	= array();
		$keys 	= array_keys($array);
		
		shuffle($keys);
		foreach($keys as $key)
		{
			$return[$key] = $array[$key];
			unset($array[$key]);
		}
		return $return;
	}

	// Premier élément d'un array
	function first($array)
	{
		return array_shift($array);
	}

	// Dernier élément d'un array
	function last($array)
	{
		return array_pop($array);
	}

	// Cherche dans un array
	function search($array, $search)
	{
		return preg_grep('#' . preg_quote($search) . '#i' , $array);
	}

	// Identique à search mais retourne un boolean
	function contains($array, $search)
	{
		$search = self::search($array, $search);
		return (empty($search)) ? false : true;
	}

	// Remplit un array avec le placeholder X
	function fill($array, $limit, $fill = 'placeholder')
	{
		if(count($array) < $limit)
		{
			$diff = $limit - count($array);
			for($x = 0; $x < $diff; $x++) $array[] = $fill;
		}
		return $array;
	}

	// Vérifie que les champs $required se trouvent dans $array
	function missing($array, $required = array())
	{
		$missing = array();
		foreach($required as $r)
			if(empty($array[$r])) $missing[] = $r;

		return $missing;
	}
	
	/*
	########################################
	########## EXPORTER UN ARRAY ###########
	########################################
	*/
	
	// Exporter au format JSON
	function json($array)
	{
		return @json_encode((array)$array);
	}

	// Exporter au format XML
	function xml($array, $tag = 'root', $head = true, $charset = 'utf-8', $tab = '  ', $level = 0)
	{
		$result  = ($level==0 && $head) ? '<?xml version="1.0" encoding="' . $charset . '"?>' . "\n" : '';
		$nlevel  = ($level+1);
		$result .= str_repeat($tab, $level) . '<' . $tag . '>' . "\n";
		
		foreach($array as $key => $value)
		{
			$key = str::lower($key);
			if(is_array($value))
			{
				$mtags = false;
				foreach($value as $key2 => $value2)
				{
					if(is_array($value2))
					{
						$result .= self::xml($value2, $key, $head, $charset, $tab, $nlevel);
					} 
					else if(trim($value2) != '')
					{
						$value2  = (htmlspecialchars($value2) != $value2) ? '<![CDATA[' . $value2 . ']]>' : $value2;
						$result .= str_repeat($tab, $nlevel) . '<' . $key . '>' . $value2 . '</' . $key . '>' . "\n";
					}
					$mtags = true;
				}
				if(!$mtags && count($value) > 0) {
					$result .= self::xml($value, $key, $head, $charset, $tab, $nlevel);
				}
			} 
			else if(trim($value) != '')
			{
				$value   = (htmlspecialchars($value) != $value) ? '<![CDATA[' . $value . ']]>' : $value;
				$result .= str_repeat($tab, $nlevel) . '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
			}
		}
		return $result . str_repeat($tab, $level) . '</' . $tag . '>' . "\n";
	}
}
?>