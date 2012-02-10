<?php
class t
{
	// Fonction de formattage des durées
	static function format($secs, $format = NULL, $modulus = true) 
	{
		if($modulus)
		{
			$restant = $secs;
			$integers = array('s' => 60, 'i' => 60, 'h' => 24, 'd' => 30.4, 'm' => 12, 'y' => 1); 
	
			foreach($integers as $v => $c)
			{
				$vals[$v] = $v == 'y' ? floor($restant) : $restant % $c;
				$restant -= a::get($vals, $v);
				$restant = $restant / $c;
			}
		}
		else
			$vals = array(
				's' => $secs,
				'i' => $secs / 60,
				'h' => $secs / 60 / 60,
				'd' => $secs / 60 / 60 / 24,
				'w' => $secs / 60 / 60 / 24 / 7,
				'm' => $secs / 60 / 60 / 24 / 30,
				'y' => $secs / 60 / 60 / 24 / 365);
		
		foreach($vals as $type => $time)
			$format = str_replace('{' .$type. '}', str_pad($time, 2, "0", STR_PAD_LEFT), $format);
		
		return $format;
	}
	
	// Calcul la différence entre deux dates
	static function difference($debut, $fin, $pattern = '{d}', $modulus = false)
	{
		$time = strtotime($fin) - strtotime($debut);
		return self::format($time, $pattern, $modulus);
	}
	
	// Calcule l'âge à partir d'une date de naissance
	static function age($date)
	{
 		list($year, $month, $day) = explode('-', $date);
 		
		$yearDiff = date('Y') - $year;
		$monthDiff = date('m') - $month;
		$dayDiff = date('d') - $day;
		
		if( ($monthDiff == 0 and $dayDiff < 0) or ($monthDiff < 0) )
			$yearDiff--;
		
		return $yearDiff;
	}
	
	/*
	########################################
	############## RACCOURCIS ##############
	########################################
	*/
	
	// 00:00:00
	static function hms($s)
	{
		return self::format($s, '{h}:{i}:{s}');
	}
	
	// 00:00
	static function ms($s)
	{
		return self::format($s, '{i}:{s}');
	}
}
?>