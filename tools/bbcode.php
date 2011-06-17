<?php
function bbcode (&$contenu, $html = 0) {
	if($html == 0)
	{
		$contenu = preg_replace('#\[b\](.+)\[/b\]#isU', '<span class="b">$1</span>', nl2br($contenu));
		$contenu = preg_replace('#\[i\](.+)\[/i\]#isU', '<span class="i">$1</span>', $contenu);
		$contenu = preg_replace('#\[u\](.+)\[/u\]#isU', '<span class="u">$1</span>', $contenu);
		$contenu = preg_replace('#\[titre\](.+)\[/titre\]#isU', '<span class="h4">$1</span><br /><br />', $contenu);
	}
	else
	{
		$contenu = preg_replace('#\[b\](.+)\[/b\]#isU', '<strong>$1</strong>', nl2br($contenu));
		$contenu = preg_replace('#\[i\](.+)\[/i\]#isU', '<em>$1</em>', $contenu);
		$contenu = preg_replace('#\[u\](.+)\[/u\]#isU', '<span style="text-decoration:underline">$1</span>', $contenu);
		$contenu = preg_replace('#\[titre\](.+)\[/titre\]#isU', '<h2 style="margin-bottom: 3px">$1</h2>', $contenu);
	}
	$contenu = preg_replace('#\[image\](.+)\[/image\]#isU', '<img src="$1" />', $contenu);
	$contenu = preg_replace('#\[taille="(.+)"\](.+)\[/taille\]#isU', '<span style="font-size:$1">$2</span>', $contenu);
	$contenu = preg_replace('#\[couleur="(.+)"\](.+)\[/couleur\]#isU', '<span style="color:$1">$2</span>', $contenu);
	$contenu = preg_replace('#\[lien\](.+)\[/lien\]#isU', '<a href="$1">$1</a>', $contenu);
	$contenu = preg_replace('#\[lien="(.+)"\](.+)\[/lien\]#isU', '<a href="$1">$2</a>', $contenu);
	return $contenu;
}
?>