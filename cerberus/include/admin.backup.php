<?php
// Suppression et chargement
if(isset($_GET['delete']))
{
	if(f::remove('cerberus/cache/sql/' .$_GET['delete']. '/')) str::display('La sauvegarde du ' .$_GET['delete']. ' a bien été supprimée');
	else str::display('Sauvegarde introuvable', 'error');
}
if(isset($_GET['load']))
{
	foreach(glob('cerberus/cache/sql/' .$_GET['load']. '/*.sql') as $file)
		$fichier = $file;
		
	multiQuery(file_get_contents($fichier), array(config::get('db.host'), config::get('db.user'), config::get('db.password'), config::get('db.name')));
	str::display('La sauvegarde du ' .$_GET['load']. ' a bien été chargée', 'success');
}

echo '<p>Ci-dessous se trouve la liste des sauvegardes journalières.</p>
<table>
	<thead>
		<tr class="entete">
			<td>Date</td>
			<td>Télécharger (format SQL)</td>
			<td>Charger la sauvegarde</td>
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
		$filesql = a::simple(glob($file. '/*.sql'));

		echo 
		'<tr>
		<td>' .$folderDate. '</td>
		<td>' .str::link($filesql, str::img('assets/css/load.png'), array('load' => $folderDate)). '</td>
		<td>' .str::slink(NULL, str::img('assets/css/load.png'), array('load' => $folderDate)). '</td>
		<td>' .str::slink(NULL, str::img('assets/css/delete.png'), array('delete' => $folderDate)). '</td>
		</tr>';
	}
}
echo '</tbody></table>';
?>