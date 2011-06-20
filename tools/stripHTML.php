<?php
function stripHTML($chain)
{
	$contenu = preg_replace('#<p>(.+)</p>#isU', '$1',$chain);
	$contenu = preg_replace('#<em>(.+)</em>#isU', '$1', $contenu);
	$contenu = preg_replace('#<p class="navbar">(.+)</p>#isU', '$1', $contenu);
	$contenu = preg_replace('#<img src="(.+)" />#isU', '', $contenu);
	$contenu = preg_replace('#<span class="(.+)">(.+)</span>#isU', '$2', $contenu);
	return $contenu;
}
?>