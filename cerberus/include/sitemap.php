<p>Ci-dessous vous trouverez un résumé de toutes les pages et sous-pages du site</p>

<table>
<tr>
<td>
<?php
global $desired;
$sitemap = $desired->get();
$lignes = floor(count($sitemap) / 1);
$count = 0;

foreach($sitemap as $categorie => $pages)
{
	if($categorie != 'admin' or (LOCAL and $categorie == 'admin'))
	{
		echo '<ul>';
		echo str::slink(
			$categorie,
			'<h2>' .l::get('menu-'.$categorie, ucfirst($categorie)). '</h2>');
		
		if(isset($pages['submenu']))
		{
			if(isset($pages['external']) and $pages['external'] != 1)
				foreach($pages['submenu'] as $pageSub => $subvalues)
				{
					echo str::slink(
						$categorie.'-'.$pageSub,
						'<li>' .l::get('menu-'.$categorie.'-'.$pageSub). '</li>');
				}
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