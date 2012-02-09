<?php
class t
{
	// Fonction de formattage des durées
	static function format($secs, $format = NULL, $modulus = true) 
	{ 
		if($modulus)
			$vals = array(
				'w' => (int) ($secs / 86400 / 7), 
				'd' => $secs / 86400 % 7, 
				'h' => $secs / 3600 % 24, 
				'm' => $secs / 60 % 60, 
				's' => $secs % 60); 
		else
			$vals = array(
				's' => $secs,
				'm' => $secs / 60,
				'h' => $secs / 60 / 60,
				'd' => $secs / 60 / 60 / 24,
				'w' => $secs / 60 / 60 / 24 / 7);
 
		foreach($vals as $type => $time)
			$format = str_replace('{' .$type. '}', str_pad($time, 2, "0", STR_PAD_LEFT), $format);
		
		return $format;
	}
	
	// Calcul la différence entre deux dates
	function difference($debut, $fin, $pattern = 'J')
	{
		// Diviseurs
		if($mode == 'J') $diviseur = 86400;
		elseif($mode == 'H') $diviseur = 3600;
		elseif($mode == 'M') $diviseur = 60;
		else $diviseur = 1;	
	
		$dateDebut = explode("-", $debut);
		$dateFin = explode("-", $fin);
		$temps = mktime(0, 0, 0, $dateFin[1], $dateFin[2], $dateFin[0]) - 
				mktime(0, 0, 0, $dateDebut[1], $dateDebut[2], $dateDebut[0]);
		return($temps / $diviseur);
	}
	
	/*
	########################################
	############## RACCOURCIS ##############
	########################################
	*/
	
	// 00:00:00
	static function hms($s)
	{
		return self::format($s, '{h}:{m}:{s}');
	}
	
	// 00:00
	static function ms($s)
	{
		return self::format($s, '{m}:{s}');
	}
}
?>