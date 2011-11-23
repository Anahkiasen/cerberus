<?php
class timer
{
	public static $timer = array();

	// Enregistre un temps
	static function set($key = 'start')
	{
		if(config::get('timer', FALSE) === false) return false;
		$time = explode(' ', microtime());
		self::$timer[$key] = (double)$time[1] + (double)$time[0];
	}

	// Récupère un/l'ensemble des temps enregistrés
	static function get($key = NULL)
	{
		if(config::get('timer', FALSE) === false) return false;
		if(!$key)
		{
			self::set('end');
			foreach(self::$timer as $key => $value)
				$benchmark[$key] = self::get($key);
				$benchmark['total'] = self::loading();
				echo '<pre style="display:none">' .print_r($benchmark, true). '</pre>';
		}
		else
		{
			$time  = explode(' ', microtime());
			$time  = (double)$time[1] + (double)$time[0];
			$timer = a::get(self::$timer, $key);
			return round(($time - $timer), 5);
		}
	}
	
	// Calcul le temps de chargement total
	static function loading()
	{
		$total = 0;
		foreach(self::$timer as $key => $value)
			$total += self::get($key);
			return $total;
	}
}
timer::set();
?>