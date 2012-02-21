<?php
class a
{
	// Gets an element of an array by key
	static function get($array, $key, $default = NULL)
	{
		if(is_array($key)) return (isset($array[$key[0]][$key[1]])) ? $array[$key[0]][$key[1]] : $default;
		else return (isset($array[$key])) ? $array[$key] : $default;
	}
	
	// Gets all elements for an array of key
	static function getall($array, $keys)
	{
		$result = array();
	    foreach($keys as $key) $result[$key] = a::get($array, $key);
	    return $result;
	}
	
	// Removes an element from an array
	static function remove($array, $search, $key = true)
	{
		if(is_array($search))
		{
			foreach($search as $s) $array = self::remove($array, $s, $key);	
			return $array;
		}
		
		if($key) unset($array[$search]);
		else
		{
			$found_all = false;
			while(!$found_all)
			{
				$index = array_search($search, $array);
				if($index !== false) unset($array[$index]);
				else $found_all = true;
			}
		}
		return $array;
	}

	// Shows an entire array or object in a human readable way
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

	// Returns the first element of an array
	static function first($array)
	{
		return array_shift($array);
	}

	// Returns the last element of an array
	static function last($array)
	{
		return array_pop($array);
	}
	
	//// Returns the medium value of an array
	static function medium($array)
	{
		return round(array_sum($array), 0) / sizeof($array); 
	}

	// Search for elements in an array by regular expression
	static function search($array, $search)
	{
		return preg_grep('#'.preg_quote($search).'#i', $array);
	}

	// Checks if an array contains a certain string
	static function contains($array, $search)
	{
		$search = self::search($array, $search);
		return !empty($search);
	}

	// Checks for missing elements in an array
	static function missing($array, $required = array())
	{
		$missing = array();
		foreach($required as $r)
			if(empty($array[$r])) $missing[] = $r;

		return $missing;
	}
	
	//// Checks if an array is associative (experimental)
	static function check_assoc($array)
	{
		return !ctype_digit(implode('', array_keys($array)));
	}
	
	/*
	########################################
	########## ACTIONS SUR ARRAY ###########
	########################################
	*/
	
	//// Force an element to be an array
	static function force_array(&$variable)
	{
		$return = !is_array($variable) ? array($variable) : $variable;
		return $return;
	}
	
	// Injects an element into an array
	static function inject($array, $position, $element = 'placeholder')
	{
		$start = array_slice($array, 0, $position);
		$end = array_slice($array, $position);
		return array_merge($start, (array)$element, $end);
	}
	
	// Shuffles an array and keeps the keys
	static function shuffle($array)
	{
		$keys = array_keys($array);
		shuffle($keys);
		return array_merge(array_flip($keys), $array);
	}
  	
	// Fills an array up with additional elements to certain amount. 
	static function fill($array, $limit, $fill = 'placeholder')
	{
		if(count($array) < $limit)
		{
			$diff = $limit - count($array);
			for($x = 0; $x < $diff; $x++) $array[] = $fill;
		}
		return $array;
	}
	
	// Sorts a multi-dimensional array by a certain column
	static function sort($array, $field, $direction = 'desc', $method = SORT_REGULAR)
	{
		$direction = (strtolower($direction) == 'desc') ? SORT_DESC : SORT_ASC;
		$helper = array();
		
		foreach($array as $key => $row)
			$helper[$key] = (is_object($row)) ? (method_exists($row, $field)) ? str::lower($row -> $field()) : str::lower($row -> $field) : str::lower($row[$field]);
		
		array_multisort($helper, $direction, $method, $array);
		return $array;
	}
	
	//// Simplify an array to its simplest form
	static function simple($array, $unarray = true)
	{
		$output = array();
		
		if(sizeof($array) == 1 and $unarray)
		{
			$output = self::get(array_values($array), 0);
			if(is_array($output)) $output = self::simple($output);
		}
		else
		{
			foreach($array as $key => $value)
			{
				if(is_array($value) and sizeof($value) == 1)
					$output[$key] = self::get(array_values($value), 0);
				else $output[$key] = $value;
			}
		}
		
		return $output;
	}

	//// Rearrange a multidimensionnal array by one of its subkey
	static function rearrange($array, $subkey = NULL, $remove = FALSE)
	{
		$output = array();
		
		foreach($array as $key => $value)
		{
			if(isset($value[$subkey]))
			{
				$output[$value[$subkey]] = $value;
				if($remove) $output[$value[$subkey]] = a::remove($output[$value[$subkey]], $subkey);
			}
			else
			{
				$keys = array_keys($value);
				$output[$value[$keys[0]]] = $value;
				if($remove) a::remove($output[$value[$subkey]], $keys[0]);
			}
		}
		
		return $output;
	}
		
	//// Implose an array with different glues (glue1 around the value, glue2 between entries)
	static function simplode($glue1, $glue2, $array, $escape = FALSE)
	{
		if(is_array($array) and !empty($glue2))
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
		else return false;
	}
				
	/*
	########################################
	###### ARRAYS MULTIDIMENSIONNELS #######
	########################################
	*/
	
	// Extracts a single column from an array
	static function extract($array, $key)
	{
		$output = array();
		foreach($array AS $a)
			if(isset($a[$key])) $output[] = $a[$key];
		return $output;
	}
	
	//// Get a precise path inside a multidimensionnal array
	static function get_path($array, $path, $default = NULL)
	{
		if(!is_array($path)) $path = explode(' ', $path);
		foreach($path as $pat)
		{
			if(isset($array[$pat])) $array = $array[$pat];
			
		}	
		return $array;
	}
		
	//// Flatten an array
	static function array_flatten($array, $return)
	{
		foreach($array as $key => $value)
		{
			if(is_array($value)) $return = self::array_flatten($value, $return);
			else if(!is_null($value)) $return[$key] = $value;
		}
		return $return;
	}
		
	/*
	########################################
	########## EXPORTER UN ARRAY ###########
	########################################
	*/
	
	// Converts an array to a JSON string
	static function json($array)
	{
		return @json_encode((array)$array);
	}

	// Converts an array to a XML string
	static function xml($array, $tag = 'root', $head = true, $charset = 'utf-8', $tab = '	', $level = 0)
	{
		$result = ($level == 0 && $head) ? '<?xml version="1.0" encoding="'.$charset.'"?>'.PHP_EOL : NULL;
		$nlevel = ($level + 1);
		$result .= str_repeat($tab, $level).'<'.$tag.'>'.PHP_EOL;
		foreach($array AS $key => $value)
		{
			$key = str::lower($key);
			if(is_array($value))
			{
				$mtags = false;
				foreach($value AS $key2 => $value2)
				{
					if(is_array($value2))
					{
						$result .= self::xml($value2, $key, $head, $charset, $tab, $nlevel);
					}
					else if(trim($value2) != '')
					{
						$value2 = (htmlspecialchars($value2) != $value2) ? '<![CDATA['.$value2.']]>' : $value2;
						$result .= str_repeat($tab, $nlevel).'<'.$key.'>'.$value2.'</'.$key.'>'.PHP_EOL;
					}
					$mtags = true;
				}
				if(!$mtags && count($value) > 0)
					$result .= self::xml($value, $key, $head, $charset, $tab, $nlevel);
			}
			else if(trim($value) != '')
			{
				$value = (htmlspecialchars($value) != $value) ? '<![CDATA['.$value.']]>' : $value;
				$result .= str_repeat($tab, $nlevel).'<'.$key.'>'.$value.'</'.$key.'>'.PHP_EOL;
			}
		}
		return $result.str_repeat($tab, $level).'</'.$tag.'>'.PHP_EOL;
	}
	
	//// Converts an array to CSV format
	static function csv($array)
	{
		$csv = NULL;
		
		foreach($array as $row)
		{
			if(!empty($csv)) $csv .= PHP_EOL;
			foreach($row as $key => $value)
				$row[$key] = '"' .stripslashes($value). '"';
				$csv .= implode(';', $row);
		}
		
		return $csv;
	}
}
?>