<?php
class timer
{
	public static $timer = array();

	// Démarre le chronométrage d'un temps
	static function start($key = 'start')
	{
		if(config::get('timer', FALSE) === false) return false;
		self::$timer[$key] = microtime(true);
	}
	
	// Sauvegarde un temps
	static function save($key = 'start')
	{
		self::$timer[$key] = round(self::get($key) * 1000, 2).' ms';
	}

	// Récupère un/l'ensemble des temps enregistrés
	static function get($key = NULL)
	{
		if(config::get('timer', FALSE) === false) return false;
		if(!$key)
		{
			self::save('end');
			self::$timer['total'] = array_sum(self::$timer);
			echo '<pre style="display:none">' .print_r(self::$timer, true). '</pre>';
		}
		else return microtime(true) - a::get(self::$timer, $key);
	}
}
timer::start();
?>