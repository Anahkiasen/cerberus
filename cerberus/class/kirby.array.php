<?php
class a
{
  /**
    * Gets an element of an array by key
    * [EDIT-CERBERUS] 
    * 
    * @param  array    $array The source array
    * @param  mixed    $key The key to look for, or a path through a multidimensionnal array under the form key1,key2,... or arrray[key1,key2,...]
    * @param  mixed    $default Optional default value, which should be returned if no element has been found
    * @return mixed
    */
	static function get($array, $key, $default = NULL)
	{
		if(str::find(',', $key)) $key = explode(',', $key);
		if(!is_array($key)) return (isset($array[$key])) ? $array[$key] : $default;
		else
		{
			foreach($key as $k)
			{
				$array = self::get($array, $k, $default);
				if($array == $default) break;
			}
			return $array;
		}
	}
	
  /**
    * Gets all elements for an array of key
    * 
    * @param  array    $array The source array
    * @keys   array    $keys An array of keys to fetch
    * @return array    An array of keys and matching values
    */
	static function getall($array, $keys)
	{
		$result = array();
			foreach($keys as $key) $result[$key] = self::get($array, $key);
			return $result;
	}
	
  /**
    * Removes an element from an array
    * 
    * @param  array   $array The source array
    * @param  mixed   $search The value or key to look for
    * @param  boolean $key Pass true to search for an key, pass false to search for an value.   
    * @return array   The result array without the removed element
    */
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
	
	/**
	 * Shortcut for a::remove by value
	 * @param array    $array Entry array
	 * @param mixed    $search Values to look for
	 * @return array   The cleaned array
	 */
	static function remove_value($array, $search)
	{
		return self::remove($array, $search, false);
	}

  /**
    * Shows an entire array or object in a human readable way
    * This is perfect for debugging
    * 
    * @param  array   $array The source array
    * @param  boolean $echo By default the result will be echoed instantly. You can switch that off here. 
    * @return mixed   If echo is false, this will return the generated array output.
    */
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

  /**
    * Returns the first element of an array
    *
    * I always have to lookup the names of that function
    * so I decided to make this shortcut which is 
    * easier to remember.
    *
    * @param  array   $array The source array
    * @return mixed   The first element
    */
	static function first($array)
	{
		return array_shift($array);
	}

  /**
    * Returns the last element of an array
    *
    * I always have to lookup the names of that function
    * so I decided to make this shortcut which is 
    * easier to remember.
    * 
    * @param  array   $array The source array
    * @return mixed   The last element
    */
	static function last($array)
	{
		return array_pop($array);
	}
	
  /**
   * Returns the average value of an array
   * [CERBERUS-ADD]
   * 
   * @param  array 	$array The source array
   * @param  int 	$decimals The number of decimals to return
   * @return int	The average value
   */
	static function average($array, $decimals = 0)
	{
		return round(array_sum($array), $decimals) / sizeof($array); 
	}

  /**
    * Search for elements in an array by regular expression
    *
    * @param  array   $array The source array
    * @param  string  $search The regular expression
    * @return array   The array of results
    */
	static function search($array, $search)
	{
		return preg_grep('#'.preg_quote($search).'#i', $array);
	}

  /**
    * Checks if an array contains a certain string
    *
    * @param  array   $array The source array
    * @param  string  $search The string to search for
    * @return boolean true: the array contains the string, false: it doesn't
    */
	static function contains($array, $search)
	{
		$search = self::search($array, $search);
		return !empty($search);
	}

  /**
    * Checks for missing elements in an array
    *
    * This is very handy to check for missing 
    * user values in a request for example. 
    * 
    * @param  array   $array The source array
    * @param  array   $required An array of required keys
    * @return array   An array of missing fields. If this is empty, nothing is missing. 
    */
	static function missing($array, $required = array())
	{
		$missing = array();
		foreach($required as $r)
			if(empty($array[$r])) $missing[] = $r;

		return $missing;
	}
	
  /**
   * Checks whether an array is associative or not (experimental)
   * [CERBERUS-ADD]
   * 
   * @param  array 		$array The array to analyze
   * @return boolean 	true: The array is associative false: It's not
   */
	static function is_associative($array)
	{
		return !ctype_digit(implode(NULL, array_keys($array)));
	}
	
	/**
	 * Checks if an array is truly empty
	 * Casual empty will return FALSE on multidimensionnal arrays if it has levels, even if they are all empty
	 * 
	 * @param array		 $array The array to check
	 * @return boolean	Empty or not
	 */
	static function array_empty($array)
	{
		if(is_array($array))
		{
			foreach($array as $value)
				if(!self::array_empty($value)) return false;
		}
		elseif(!empty($array)) return false;
		
		return true;
	}
	
	/*
	########################################
	########## ACTIONS SUR ARRAY ###########
	########################################
	*/
	
  /**
   * Forces a variable to be an array
   * [CERBERUS-ADD]
   * 
   * @param  mixed	$mixed The value to transform in an array
   * @return array 	The entry value if it's already an array, or an array containing the value if it's not 
   */
  static function force_array(&$mixed)
  {
    return !is_array($mixed) ? array($mixed) : $mixed;;
  }
	
  /**
    * Injects an element into an array
    * 
    * @param  array   $array The source array
    * @param  int     $position The position, where to inject the element
    * @param  mixed   $element The element, which should be injected
    * @return array   The result array including the new element
    */
	static function inject($array, $position, $element = 'placeholder')
	{
		$start = array_slice($array, 0, $position);
		$end = array_slice($array, $position);
		return array_merge($start, (array)$element, $end);
	}
	
  /**
    * Shuffles an array and keeps the keys
    * 
    * @param  array   $array The source array
    * @return array   The shuffled result array
    */
	static function shuffle($array)
	{
		$keys = array_keys($array);
		shuffle($keys);
		return array_merge(array_flip($keys), $array);
	}
		
  /**
    * Fills an array up with additional elements to certain amount. 
    *
    * @param  array   $array The source array
    * @param  int     $limit The number of elements the array should contain after filling it up. 
    * @param  mixed   $fill The element, which should be used to fill the array
    * @return array   The filled-up result array
    */
	static function fill($array, $limit, $fill = 'placeholder')
	{
		if(count($array) < $limit)
		{
			$diff = $limit - count($array);
			for($x = 0; $x < $diff; $x++) $array[] = $fill;
		}
		return $array;
	}
	
  /**
    * Sorts a multi-dimensional array by a certain column
    *
    * @param  array   $array The source array
    * @param  string  $field The name of the column
    * @param  string  $direction desc (descending) or asc (ascending)
    * @param  const   $method A PHP sort method flag. 
    * @return array   The sorted array
    */
	static function sort($array, $field, $direction = 'desc', $method = SORT_REGULAR)
	{
		$direction = (strtolower($direction) == 'desc') ? SORT_DESC : SORT_ASC;
		$helper = array();
		
		foreach($array as $key => $row)
			$helper[$key] = (is_object($row)) ? (method_exists($row, $field)) ? str::lower($row -> $field()) : str::lower($row -> $field) : str::lower($row[$field]);
		
		array_multisort($helper, $direction, $method, $array);
		return $array;
	}
	
  /**
   * Reduces an array (most often the result of a query) to its simplest form
   * [CERBERUS-ADD]
   * 
   * @param  array 		$array The array to simplify
   * @param  boolean 	$stay_array Allows the function to be transformed into a string if it only contains one value
   * @return mixed 		Either an array simplified, or a single mixed value
   */
	static function simplify($array, $unarray = true, $rearrange = NULL)
	{
		if($rearrange) $array = self::rearrange($array, $rearrange, true);
		$output = array();
		
		if(sizeof($array) == 1 and $unarray)
		{
			$output = self::get(array_values($array), 0);
			if(is_array($output)) $output = self::simplify($output);
		}
		else
		{
			foreach($array as $key => $value)
			{
				if(is_array($value) and sizeof($value) == 1)
					$output[$key] = self::simplify($value);
				else $output[$key] = $value;
			}
		}
		
		return $output;
	}

  /**
   * Rearrange an array by one of it's subkeys
   * [CERBERUS-ADD]
   * 
   * Takes per example an array array(0 => array('id' => 'key1', 'value' => 'value1'), array('id' => 'key2', 'value' => 'value2'))
   * And rearrange it as array('key1' => array('value' => 'value1'), 'key2' => array('value' => 'value2'))
   * 
   * @param  array 		$array The array to rearrange
   * @param  string 	$subkey The subkey to use as the new key
   * @param  boolean 	$remove Remove or not the subkey from the original values
   * @return array 		The rearranged array
   */
	static function rearrange($array, $subkey = NULL, $remove = FALSE)
	{
		$output = array();
		
		foreach($array as $key => $value)
		{
			if(isset($value[$subkey]))
			{
				$output[$value[$subkey]] = $value;
				if($remove) $output[$value[$subkey]] = self::remove($output[$value[$subkey]], $subkey);
			}
			else
			{
				$keys = array_keys($value);
				$output[$value[$keys[0]]] = $value;
				if($remove) self::remove($output[$value[$subkey]], $keys[0]);
			}
		}
		
		return $output;
	}
	
	/**
	 * Implode an array by a set of glues
	 * Also a shortcut for implode but with array first (more logical)
	 * Useful per example to take an array and output KEY="VALUE",KEY="VALUE" by doing glue($array, ',', '="', '"')
	 * [CERBERUS-ADD]
	 * 
	 * @param array 	The array to glue
	 * @param string 	$glue_pair The glue that will go around the KEY=VALUE pairs
	 * @param string 	$glue_value The glue that will go around the values
	 * @param string	If set, $glue_value will go before the value and $glue_value_after will go after
	 * 					If not, $glue_value will go before and after the value
	 * @return string The glued array
	 */
	static function glue($array, $glue_pair, $glue_value = NULL, $glue_value_after = NULL)
	{
		if(!is_array($array)) return FALSE;
	
		if(empty($glue_value)) $imploded = $array;
		else
		{
			$imploded = array();
			foreach($array as $key => $value)
				$imploded[] = $key.$glue_value.$value.$glue_value_after;
		}
		return implode($glue_pair, $imploded);
	}
				
  /**
    * Extracts a single column from an array
    * 
    * @param  array   $array The source array
    * @param  string  $key The key name of the column to extract
    * @return array   The result array with all values from that column. 
    */
	static function extract($array, $key)
	{
		$output = array();
		foreach($array AS $a)
			if(isset($a[$key])) $output[] = $a[$key];
		return $output;
	}
		
	/*
	########################################
	########## EXPORTER UN ARRAY ###########
	########################################
	*/
	
  /**
    * Converts an array to a JSON string
    * It's basically a shortcut for json_encode()
    * 
    * @param  array   $array The source array
    * @return string  The JSON string
    */
	static function json($array)
	{
		return @json_encode((array)$array);
	}

  /**
    * Converts an array to a XML string
    * 
    * @param  array   $array The source array
    * @param  string  $tag The name of the root element
    * @param  boolean $head Include the xml declaration head or not
    * @param  string  $charset The charset, which should be used for the header
    * @param  int     $level The indendation level
    * @return string  The XML string
    */
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
	
  /**
    * Converts an array to CSV format
    * 
    * @param  array   $array The source array
    * @param  string  $delimiter The delimiter between fields, default ;
    * @return string  The CSV string
    */
	static function csv($array, $delimiter = ';')
	{
		$csv = NULL;
		foreach($array as $row)
		{
			if(!empty($csv)) $csv .= PHP_EOL;
			foreach($row as $key => $value)
				$row[$key] = '"' .stripslashes($value). '"';
				$csv .= implode($delimiter, $row);
		}
		return $csv;
	}
}
?>