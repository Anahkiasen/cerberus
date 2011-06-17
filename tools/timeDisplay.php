<?php
function timeDisplay($time, $input = 'S', $displayHour = true)
{
	$arrayInput = array(
	'S' => 1,
	'M' => 60,
	'H' => 3600);
	$time *= $arrayInput[$input];
	
	$hour = floor($time / 3600);
	$time -= $hour * 3600;
	$minutes = floor($time / 60);
	$time -= $minutes * 60;
	$seconds = $time;
	
	if($displayHour == true) return numPad($hour). ':' .numPad($minutes). ':' .numPad($seconds);
	else return numPad($minutes). ':' .numPad($seconds);
}
?>