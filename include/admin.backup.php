<?php
// Suppression et chargement
if(isset($_GET['delete']))
{
	$path = 'cerberus/cache/sql/' .$_GET['delete']. '/';
	if(file_exists($path))
	{
		sunlink($path);
		echo display('La sauvegarde du ' .$_GET['delete']. ' a bien été supprimée');
	}
	else echo display('Sauvegarde introuvable');
}
if(isset($_GET['load']))
{
	include('cerberus/conf.php');
	foreach(glob('cerberus/cache/sql/' .$_GET['load']. '/*.sql') as $file)
		$fichier = $file;
		
	multiQuery(file_get_contents($fichier), array($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB));
	echo display('La sauvegarde du ' .$_GET['load']. ' a bien été chargée');
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
		<td><a href="' .rewrite('admin-backup', array('load' => $folderDate)). '"><img src="assets/css/load.png" /></a></td>
		<td><a href="' .rewrite('admin-backup', array('delete' => $folderDate)). '"><img src="assets/css/delete.png" /></a></td>
		</tr>';
	}
}  
echo '</tbody></table>';

?>