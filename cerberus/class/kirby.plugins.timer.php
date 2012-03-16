<?php
class timer
{
	public static $timer = array();

	/* Démarre le chronométrage d'un temps */
	static function start($key = NULL)
	{
		$microtime = microtime(true);
		if($key) self::$timer[$key] = $microtime;
		else self::$timer[] = $microtime;
	}
	
	/* Sauvegarde un temps */
	static function save($key = NULL)
	{
		if(!$key) $key = a::get( array_keys(self::$timer), (sizeof(self::$timer)-1) );
		self::$timer[$key] = round(self::get($key) * 1000, 2).' ms';
	}

	/* Récupère un/l'ensemble des temps enregistrés */
	static function get($key = NULL)
	{
		if(!$key)
		{
			self::save();
			self::$timer['total'] = array_sum(self::$timer);
			a::show(self::$timer);
		}
		else return microtime(true) - a::get(self::$timer, $key);
	}
	
	static function show()
	{
		self::$timer = a::remove(self::$timer, 0);
		$total = self::$timer['total'] = round(array_sum(self::$timer), 2). ' ms';
	
		echo '<pre>';
		foreach(self::$timer as $key => $time)
		{
			$last_time = $time;
			echo 
				$key.
				str_repeat( ' ', (20 - strlen($key)) ).
				$time.
				str_repeat(' ', 20 - (strlen($time)) ).
				round($time * 100 / $total, 0).
				'%<br/>';
		}
		echo '</pre>';
	}
}
timer::start();
?>