<?php
function dateDifference($debut, $fin, $mode = 'J')
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
?>