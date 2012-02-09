<?php
class config
{
	private static $config = array();
	private static $config_file = 'cerberus/conf.php';
	
	// Charger un fichier config
	static function load($file)
	{
		if(file_exists($file)) require_once($file);
		if(isset($config)) self::set($config);
		return self::get();
	}
	
	// Récupérer une valeur config
	static function get($key = NULL, $default = NULL)
	{
		if(empty($key)) return self::$config;
		return a::get(self::$config, $key, $default);
	}
	
	// Changer une valeur config
	static function set($key, $value = NULL)
	{
		if(is_array($key)) self::$config = array_merge(self::$config, $key);
		else self::$config[$key] = $value;
	}
	
	// Ajoute une clé au fichier config
	static function hardcode($key, $value = NULL)
	{
		// Traitement de la valeur
		if(is_array($value)) $value = 'array(\'' .implode("', '", $formatted_value). '\')';
		elseif(is_bool($value)) $value = str::boolprint($value);
		elseif(is_null($value)) $value = 'NULL';
		
		$config = f::read(self::$config_file);
		
		// Recherche de sa présence dans le fichier config
		if(preg_match('#\$config\[\'(' .$key. ')\'\] = (.+);#', $config))
		{
			$config = preg_replace(
				'#\$config\[\'(' .$key. ')\'\] = (.+);#',
				'$config[\'$1\'] = ' .$value. ';',
				$config);
		}
		else if(!empty($value)) $config = str_replace('?>', '$config[\'' .$key. '\'] = "' .$value. "\";\n?>", $config);
		
		$config = f::write(self::$config_file, $config);
	}
}
?>