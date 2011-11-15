<?php
// Suppression et chargement
if(isset($_GET['delete']))
{
	if(f::remove('cerberus/cache/sql/' .$_GET['delete']. '/')) prompt('La sauvegarde du ' .$_GET['delete']. ' a bien été supprimée');
	else prompt('Sauvegarde introuvable');
}
if(isset($_GET['load']))
{
	foreach(glob('cerberus/cache/sql/' .$_GET['load']. '/*.sql') as $file)
		$fichier = $file;
		
	multiQuery(file_get_contents($fichier), array(config::get('db.host'), config::get('db.user'), config::get('db.password'), config::get('db.name')));
	prompt('La sauvegarde du ' .$_GET['load']. ' a bien été chargée');
}

echo '<p>Ci-dessous se trouve la liste des sauvegardes journalières.</p>
<table>
	<thead>
		<tr class="entete">
			<td>Date</td>
			<td>Charger</td>
			<td>Supprimer</td>
		</tr>
	</thead>
	<tbody>';
	
// Liste des sauvegardes
foreach(glob('./cerberus/cache/sql/*') as $file)
{
	if(is_dir($file))
	{
		$folderDate = str_replace('./cerberus/cache/sql/', '', $file);
		echo 
		'<tr>
		<td>' .$folderDate. '</td>
		<td>' .str::slink(NULL, str::img('assets/css/load.png'), array('load' => $folderDate)). '</td>
		<td>' .str::slink(NULL, str::img('assets/css/delete.png'), array('delete' => $folderDate)). '</td>
		</tr>';
	}
}
echo '</tbody></table>';
?>