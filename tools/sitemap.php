<?php
function sitemap()
{
	global $meta;
	global $navigation;
	
	// Navigation globale
	unset($navigation['admin']);
	foreach($navigation as $key => $value)
	{
		$final[] = $key. '/';
		foreach($value as $pages)
		{
			$final[] = $key. '/' .$pages. '/';
			$final[] = rewrite($key. '-' .$pages, array('html' => $meta[$key. '-' .$pages]['url']));
		}
	}
	
	// News
	$news = mysqlQuery('SELECT id, titre FROM news ORDER BY id ASC');
	foreach($news as $key => $value)
	{
		if(in_array('archives', $navigation['actualite'])) $final[] = rewrite('actualite-archives', array('actualite' => $key, 'html' => $value));
		$final[] = rewrite('actualite', array('actualite' => $key, 'html' => $value));
	}
	
	// Galerie
	$galeries = array('all', 'amandier', 'cuisine', 'slider', 'boutique', 'roger', 'ecole');
	foreach($galeries as $thisg)
	{
		$final[] = 'galerie/' .$thisg. '/';
		$final[] = 'galerie/galerie/' .$thisg. '/';
		$final[] = rewrite('galerie', array('galerie' => $thisg));
	}
	
	$html = array();
	foreach($final as $key => $value)
	{
		if(findString('.html', $value))
		{
			$html[] = $value;
			unset($final[$key]);
		}
	}
	
	echo '<div style="display:none">' .implode("\n", $final).implode("\n", $html). '</div>';
}
?>