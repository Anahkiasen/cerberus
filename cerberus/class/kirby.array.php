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
	
	// Supprime une entrée d'un tableau par valeur
	static function splice($array, $values)
	{
		if(!is_array($values)) $values = array($values);
		foreach($values as $value)
		{
			if(!in_array($value, $array)) continue;
			else
			{
				$index = array_search($value, $array);
				unset($array[$index]);
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
	
	// Vérifie si l'array donné est associatif
	function check_assoc($array)
	{
		return !ctype_digit(implode('', array_keys($array)));
	}
	
	/*
	########################################
	########## ACTIONS SUR ARRAY ###########
	########################################
	*/
	
	// Force un élément à être un array
	static function force_array(&$variable)
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

	// Shuffle un array en conservant les paires key/value
	static function shuffle($array)
	{
		$keys = array_keys($array); 
		shuffle($keys); 
		return array_merge(array_flip($keys), $array); 
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
	static function simple($array, $disarray = true)
	{
		if(count($array) == 1 and $disarray)
		{
			$output = self::get(array_values($array), 0);
			if(is_array($output)) $output = self::simple($output);
		}
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
		
	// Implose un array via différentes glues (glue 1 autour de la valeur, glue 2 entre les entrées)
	static function simplode($glue1, $glue2, $array, $escape = FALSE)
	{
		if(is_array($array))
		{
			if(empty($glue2))
			{
				// WIP
			}
			else
			{
				$plainedArray = array();
				foreach($array as $key => $value)
				{	
					if($escape) $value = db::escape($value);
					if(is_array($glue1)) $plainedArray[] = $key.$glue1[0].$value.$glue1[1];
					else $plainedArray[] = $key.$glue1.$value;
				}
				return implode($glue2, $plainedArray);
			}
		}
	}
	
	// Trie un array selon une requête ORDER BY
	static function sort($array, $field, $direction = 'desc', $method = SORT_REGULAR)
	{
		$direction = (strtolower($direction) == 'desc') ? SORT_DESC : SORT_ASC;
		$helper = array();
		foreach($array as $key = > $row)
			$helper[$key] = (is_object($row)) ? (method_exists($row, $field)) ? str::lower($row - > $field()) : str::lower($row - > $field) : str::lower($row[$field]);
		
		array_multisort($helper, $direction, $method, $array);
		return $array;
	}
				
	/*
	########################################
	###### ARRAYS MULTIDIMENSIONNELS #######
	########################################
	*/
	
	// Extraire un champ d'un array multidimensionnel
	static function extract($array, $key)
	{
		$output = array();
		foreach($array as $a) if(isset($a[$key])) $output[] = $a[$key];
		return $output;
	}
	
	// Récupère un chemin précis dans un array multidimensionnel
	static function get_path($array, $path, $default = NULL)
	{
		if(!is_array($path)) $path = explode(' ', $path);
		foreach($path as $pat)
		{
			if(isset($array[$pat])) $array = $array[$pat];
			
		}
		
		return $array;
	}
	
	// Supprime un chemin precis dans un array
	static function remove_path($array, $path)
	{
		$path = explode(' ', $path);
		
		$array_start = $array;
		$array = self::get_path($array, $path);
		
		asort($path);
		foreach($path as $pat)
			$array = array($pat => $array);
		
		return a::get(self::array_diff_assoc_multi($array_start, $array), 0);
	}
	
	// Trie un array multidimensionnel par une sous-clé
	static function subsort($array, $index, $order = 'ASC', $natsort = TRUE, $case_sensitive = FALSE) 
	{
		if(is_array($array) and count($array) > 0) 
		{
			foreach(array_keys($array) as $key) 
				$temp[$key] = $array[$key][$index];
			
			if(!$natsort) 
			{
				($order == 'ASC')
					? asort($temp)
					: arsort($temp);
			}
			else 
			{
				($case_sensitive)
					? natsort($temp)
					: natcasesort($temp);
					
				if($order != 'ASC') 
					$temp = array_reverse($temp, TRUE);
			}
			
			foreach(array_keys($temp) as $key)
				(is_numeric($key))
					? $sorted[] = $array[$key] 
					: $sorted[$key] = $array[$key];
					
			return $sorted;
		}
		return $array;
	}
	
	// Applatit un array multidimensionnel
	static function array_flatten($array, $return)
	{
		foreach($array as $key => $value)
		{
			if(is_array($value)) $return = self::array_flatten($value, $return);
			else if(!is_null($value)) $return[$key] = $value;
		}
		return $return;
	}
	
	// Extension de la fonction array_diff_assoc pour fonctionner avec les arays multidimensionnels
	static function array_diff_assoc_multi($array1, $array2)
	{ 
		$diff = false; 
		// Left-to-right 
		foreach($array1 as $key => $value)
		{ 
			if(!array_key_exists($key, $array2)) $diff[0][$key] = $value; 
			elseif(is_array($value))
			{ 
				if(!is_array($array2[$key]))
				{ 
					$diff[0][$key] = $value; 
					$diff[1][$key] = $array2[$key]; 
				}
				else
				{ 
					$new = self::array_diff_assoc_multi($value, $array2[$key]); 
					if($new !== false)
					{ 
						if (isset($new[0])) $diff[0][$key] = $new[0]; 
						if (isset($new[1])) $diff[1][$key] = $new[1]; 
					}
				}
			}
			elseif($array2[$key] !== $value)
			{ 
				$diff[0][$key] = $value; 
				$diff[1][$key] = $array2[$key]; 
			}
		} 
		
		// Right-to-left 
		foreach($array2 as $key => $value)
		{ 
			if(!array_key_exists($key, $array1)) $diff[1][$key] = $value; 
		}
		return $diff;
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
		$result = ($level == 0 && $head) ? '<?xml version="1.0" encoding="' . $charset . '"?>' . "\n" : NULL;
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
				if(!$mtags && count($value) > 0) $result .= self::xml($value, $key, $head, $charset, $tab, $nlevel);
			} 
			else if(trim($value) != '')
			{
				$value = (htmlspecialchars($value) != $value) ? '<![CDATA[' . $value . ']]>' : $value;
				$result .= str_repeat($tab, $nlevel) . '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
			}
		}
		return $result . str_repeat($tab, $level) . '</' . $tag . '>' . "\n";
	}
	
	// Exporter au format CSV
	static function csv($array, $filename = 'this', $entete = NULL)
	{
		$csv = $entete;
		$ligne = NULL;
		
		foreach($array as $ligne => $colonne)
		{
			if(!empty($csv)) $csv .= "\n";
			foreach($colonne as $key => $value)
				$colonne[$key] = '"' .stripslashes($value). '"';
				$csv .= implode(';', $colonne);
		}

		f::write($filename. '.csv', $csv);
	}
}
?>