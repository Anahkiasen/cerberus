<p>Ci-dessous vous trouverez un résumé de toutes les pages et sous-pages du site</p>

<table>
<tr>
<td>
<?php
global $desired;
$sitemap = $desired->get();
$lignes = floor(count($sitemap) / 3);
$count = 0;

foreach($sitemap as $categorie => $pages)
{
	if($categorie != 'admin' or (LOCAL and $categorie == 'admin'))
	{
		echo '<ul>';
		echo str::slink(
			$categorie,
			'<h2>' .l::get('menu-'.$categorie). '</h2>');
		
		foreach($desired->get($categorie) as $pages)
		{
			echo str::slink(
				$categorie.'-'.$pages,
				'<li>' .l::get('menu-'.$categorie.'-'.$pages, ucfirst($pages)). '</li>');
		}
		echo '</ul>';
		
		// Colonnes
		$count++;
		if($count == $lignes)
		{
			$count = 0;
			echo '</td><td>';
		}
	}
}
?>
</td>
</tr>
</table>