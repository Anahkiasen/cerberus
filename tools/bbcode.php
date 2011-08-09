<?php
/*
	Fonction bbcode
	# Formate une chaîne selon des REGEX de mise en forme
	
	$contenu
		La chaine à formater
	$css
		Utilisation ou non de classes CSS pour la mise en forme
*/
function bbcode($contenu, $css = TRUE)
{
	if($css == FALSE)
	{
		$contenu = preg_replace('#\[b\](.+)\[/b\]#isU', '<span class="b">$1</span>', $contenu);
		$contenu = preg_replace('#\[i\](.+)\[/i\]#isU', '<span class="i">$1</span>', $contenu);
		$contenu = preg_replace('#\[u\](.+)\[/u\]#isU', '<span class="u">$1</span>', $contenu);
		$contenu = preg_replace('#\[titre\](.+)\[/titre\]#isU', '<span class="h4">$1</span><br /><br />', $contenu);
	}
	else
	{
		$contenu = preg_replace('#\[b\](.+)\[/b\]#isU', '<strong>$1</strong>', $contenu);
		$contenu = preg_replace('#\[i\](.+)\[/i\]#isU', '<em>$1</em>', $contenu);
		$contenu = preg_replace('#\[u\](.+)\[/u\]#isU', '<ins>$1</ins>', $contenu);
		$contenu = preg_replace('#\[titre\](.+)\[/titre\]#isU', '<h2 style="margin-bottom: 3px">$1</h2>', $contenu);
	}
	$contenu = preg_replace('#\[image\](.+)\[/image\]#isU', '<img src="$1" />', $contenu);
	$contenu = preg_replace('#\[taille="(.+)"\](.+)\[/taille\]#isU', '<span style="font-size:$1">$2</span>', $contenu);
	$contenu = preg_replace('#\[couleur="(.+)"\](.+)\[/couleur\]#isU', '<span style="color:$1">$2</span>', $contenu);
	$contenu = preg_replace('#\[lien\](.+)\[/lien\]#isU', '<a href="$1">$1</a>', $contenu);
	$contenu = preg_replace('#\[lien=(.+)\](.+)\[/lien\]#isU', '<a href="$1">$2</a>', $contenu);
	
	return $contenu;
}
?>