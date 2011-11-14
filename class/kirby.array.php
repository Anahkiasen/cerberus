<?php
class a
{
	// Récupérer une entrée d'un array - retourne $default si inexistant
	static function get($array, $key, $default = NULL)
	{
		if(is_array($key)) return (isset($array[$key[0]][$key[1]])) ? $array[$key[0]][$key[1]] : $default;
		else return (isset($array[$key])) ? $array[$key] : $default;
	}
	
	// Supprimer une/des entrée(s) d'un tableau
	static function remove($array, $search, $multiple = FALSE)
	{
		if(!is_array($search)) $search = array($search);
		foreach($search as $searchkey)
		{
			if(!$multiple) unset($array[$searchkey]);
			else 
			{
				$found_all = FALSE;
				while(!$found_all)
				{
					$index = array_search($searchkey, $array);
					if($index !== false) unset($array[$index]);
					else $found_all = true;
				}
			}
		}
		return $array;
	}
	
	// Affiche un array
	static function show($array, $echo = true)
	{
		$output = '<pre>';
		$output .= htmlspecialchars(print_r($array, true));
		$output .= '</pre>';
		
		if($echo) echo $output;
		else return $output;
	}
	
	/*
	########################################
	########## INFOS ABOUT ARRAY ###########
	########################################
	*/

	// Premier élément d'un array
	static function first($array)
	{
		return array_shift($array);
	}

	// Dernier élément d'un array
	static function last($array)
	{
		return array_pop($array);
	}
	
	// Moyenne d'un array
	static function medium($array)
	{
		return round(array_sum($array), 0) / sizeof($array); 
	}

	// Cherche dans un array
	static function search($array, $search)
	{
		return preg_grep('#' . preg_quote($search) . '#i' , $array);
	}

	// Identique à search mais retourne un boolean
	static function contains($array, $search)
	{
		$search = self::search($array, $search);
		return (empty($search)) ? false : true;
	}

	// Vérifie que les champs $required se trouvent dans $array
	static function missing($array, $required = array())
	{
		$missing = array();
		foreach($required as $r)
			if(empty($array[$r])) $missing[] = $r;

		return $missing;
	}
	
	/*
	########################################
	########## ACTIONS SUR ARRAY ###########
	########################################
	*/
	
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
	static function inject($array, $position, $element = 'placeholder')
	{
		$start = array_slice($array, 0, $position);
		$end = array_slice($array, $position);
		return array_merge($start, (array)$element, $end);
	}

	// Extraire un champ d'un array multidimensionnel
	static function extract($array, $key)
	{
		$output = array();
		foreach($array as $a) if(isset($a[$key])) $output[] = $a[$key];
		return $output;
	}

	// Shuffle un array en conservant les paires key/value
	static function shuffle($array)
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
	
	// Remplit un array avec le placeholder X
	static function fill($array, $limit, $fill = 'placeholder')
	{
		if(count($array) < $limit)
		{
			$diff = $limit - count($array);
			for($x = 0; $x < $diff; $x++) $array[] = $fill;
		}
		return $array;
	}
	
	// Simplifie un array
	static function simple($array)
	{
		if(count($array) == 1)
			$output = self::get(array_values($array), 0);
		else
		{
			foreach($array as $key => $value)
			{
				if(is_array($value) and count($value) == 1)
					$output[$key] = self::get(array_values($value), 0);
				else $output[$key] = $value;
			}
		}
		if(!isset($output)) $output = array();
		return $output;
	}

	// Echange une clé et une sous-clé
	static function rearrange($array, $subkey = NULL, $remove = FALSE)
	{
		$output = array();
		foreach($array as $key => $value)
		{
			if(isset($value[$subkey]))
			{
				$output[$value[$subkey]] = $value;
				if($remove == TRUE) unset($output[$value[$subkey]][$subkey]);
			}
			else
			{
				$keys = array_keys($value);
				$output[$value[$keys[0]]] = $value;
				if($remove == TRUE) unset($output[$value[$subkey]][$keys[0]]);
			}
		}
		return $output;
	}

	/*
	########################################
	########## EXPORTER UN ARRAY ###########
	########################################
	*/
	
	// Exporter au format JSON
	static function json($array)
	{
		return @json_encode((array)$array);
	}

	// Exporter au format XML
	static function xml($array, $tag = 'root', $head = true, $charset = 'utf-8', $tab = ' ', $level = 0)
	{
		$result = ($level == 0 && $head) ? '<?xml version="1.0" encoding="' . $charset . '"?>' . "\n" : '';
		$nlevel = ($level+1);
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
						$value2 = (htmlspecialchars($value2) != $value2) ? '<![CDATA[' . $value2 . ']]>' : $value2;
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
				$value = (htmlspecialchars($value) != $value) ? '<![CDATA[' . $value . ']]>' : $value;
				$result .= str_repeat($tab, $nlevel) . '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
			}
		}
		return $result . str_repeat($tab, $level) . '</' . $tag . '>' . "\n";
	}
}
?>