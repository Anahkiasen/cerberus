<?php
// Suppression et chargement
if(isset($_GET['delete']))
{
	if(f::remove(PATH_CACHE. 'sql/' .$_GET['delete']. '/')) str::display('La sauvegarde du ' .$_GET['delete']. ' a bien été supprimée');
	else str::display('Sauvegarde introuvable', 'error');
}
if(isset($_GET['load']))
{
	foreach(glob(PATH_CACHE. 'sql/' .$_GET['load']. '/*.sql') as $file)
		$fichier = $file;

	multiQuery(file_get_contents($fichier), array(config::get('db.host'), config::get('db.user'), config::get('db.password'), config::get('db.name')));
	str::display('La sauvegarde du ' .$_GET['load']. ' a bien été chargée', 'success');
}

echo '<p>Ci-dessous se trouve la liste des sauvegardes journalières.</p>
<table class="table table-bordered table-condensed table-striped">
	<thead>
		<tr>
			<th>Date</th>
			<th>Télécharger (format SQL)</th>
			<th>Charger la sauvegarde</th>
			<th>Supprimer</th>
		</tr>
	</thead>
	<tbody>';

// Liste des sauvegardes
foreach(glob('./' .PATH_CACHE. 'sql/*') as $file)
{
	if(is_dir($file))
	{
		$folderDate = str_replace('./' .PATH_CACHE. 'sql/', '', $file);
		$filesql = a::simplify(glob($file. '/*.sql'));

		echo
		'<tr>
		<td>' .$folderDate. '</td>
		<td>' .str::link($filesql, str::img(PATH_CERBERUS.'img/action-load.png'), array('load' => $folderDate)). '</td>
		<td>' .str::slink(NULL, str::img(PATH_CERBERUS.'img/action-load.png'), array('load' => $folderDate)). '</td>
		<td>' .str::slink(NULL, str::img(PATH_CERBERUS.'img/action-delete.png'), array('delete' => $folderDate)). '</td>
		</tr>';
	}
}
echo '</tbody></table>';
?>
