<h1>Dossiers d'images</h1>

<?php
// Préfixe
if(isset($_POST['prefixpost'])) $_SESSION['prefix'] = $_POST['prefixpost'];
if(isset($_GET['noprefix'])) unset($_SESSION['prefix']);
$PREFIXE = a::get($_SESSION, 'prefix', NULL);

// Supprimer un dossier
if(isset($_GET['deleteFolder']))
{
	if(f::remove('assets/file/' .$_GET['deleteFolder']. '/')) prompt('Le dossier a bien été supprimé');
	else prompt('Une erreur est survenue durant la suppression du dossier');
}

// Supprimer une image
if(isset($_GET['delete_image']))
{
	if(f::remove('assets/file/' .$_GET['pictures']. '/' .$_GET['delete_image']))
		prompt('Image ' .$_GET['delete_image']. ' supprimée');
}

// Renommer une image
if(isset($_POST['oldfile']))
{
	$dossier = a::get(explode('/', $_POST['oldfile']), 2);
	$extension = f::extension($_POST['oldfile']);
	rename($_POST['oldfile'], 'assets/file/' .$dossier. '/' .str::slugify($_POST['renommer']). '.jpg');
	prompt('Le fichier a bien été renommé');
}

// Renommer les images en masse
if(isset($_GET['rename']))
{
	$i = 0;
	$basename = $_GET['rename'];
	$glob = glob('assets/file/' .$basename. '/*');
	
	// Nombre de 0
	$count = count($glob);
	if($count < 10) $numpad = 1;
	elseif($count >= 10 and $count < 100) $numpad = 2;
	elseif($count >= 100 and $count < 1000) $numpad = 3;
	elseif($count >= 1000) $numpad = 4;
	
	foreach($glob as $file)
	{
		$extension = f::extension($file);
		$newname = $PREFIXE.$basename. '-' .str_pad($i, $numpad, "0", STR_PAD_LEFT). '.' .$extension;
		rename($file, 'assets/file/' .$basename. '/' .$newname);
		
		$i++;
	}
	
	prompt('Les images ont bien été renommées au format ' .$PREFIXE.$basename. '-XX');
}
?>

<form method="post" action="<?= rewrite('admin-images') ?>">
<p>Ajouter un préfixe au renommage automatique (ou <?= str::slink('admin-images', 'supprimer le préfixe enregistré', 'noprefix') ?>) :<br />
<input type="text" name="prefixpost" value="<?= $PREFIXE ?>" style="padding:5px" /> <input type="submit" value="OK" class="ok" />
</form>

<table>
<thead>
	<tr>
		<td>Dossier</td>
		<td>Nombre d'images</td>
		<td>Renommer les images automatiquement</td>
		<td>Voir les images</td>
		<td>Supprimer</td>
	</tr>
</thead>
<tbody>
<?php
// Liste des dossiers d'images
foreach(glob('assets/file/*') as $file)
{
	if(is_dir($file) and !in_array(f::filename($file), array('cache', 'news')))
	{
		$basename = f::filename($file);
		$count = count(glob('assets/file/' .$basename. '/*'));
	
		echo '
		<tr>
			<td>' .$basename. '</td>
			<td>' .$count. '</td>
			<td>' .str::slink(NULL, $PREFIXE.$basename.'-XX.jpg', array('rename' => $basename)). '</td>
			<td>' .str::slink(NULL, str::img('assets/css/picture.png', 'Voir les images'), array('pictures' => $basename)). '</td>
			<td>' .str::slink(NULL, str::img('assets/css/delete.png', 'Supprimer le dossier'), array('deleteFolder' => $basename)). '</td>
		</tr>';
	}
}
?>
</tbody>
</table>

<?php
// Afficher les images
if(isset($_GET['pictures']) and file_exists('assets/file/' .$_GET['pictures']))
{
	$picpath = 'assets/file/' .$_GET['pictures']. '/';

	$images = glob($picpath. '*');
	echo '
	<h1>Afficher les images de <em>' .$_GET['pictures']. '</em></h1>
	<table id="imagemanager">
		<thead>
			<tr>
				<td>Image</td>
				<td>Chemin vers l\'image</td>
				<td>Renommer</td>
				<td style="width: 20px">Supprimer</td>
			</tr>
		</thead>
	<tbody>';
	
	if($images) foreach($images as $image)
	{
		$basename = f::filename($image);
		echo
		'<tr>
			<td><img src="' .timthumb($_GET['pictures']. '/' .$basename, 150, 100). '" /></td>
			<td>' .$image. '</td>
			<td>
				<form method="post">
				<input type="text" name="renommer" style="width:70%" /><input type="submit" value="OK" style="width:30px; position: relative; top: -5px; left:-5px" />
				<input type="hidden" name="oldfile" value="' .$image. '" />
				</form>
				</td>
			<td>' .str::slink('admin-images', str::img('assets/css/delete.png'), array('pictures' => $_GET['pictures'], 'delete_image' => $basename)). '</td>
		</tr>';
	}
	echo '</tbody></table>';
	
	// Renommer une image
	if(isset($_GET['edit_image'])) 
	{
		$name = str_replace('\\', '', $_GET['edit_image']);
		
		$editImage = new form(false);
		$editImage->openFieldset('Editer une image');
			$editImage->addText('Renommer', '<strong>' .$name. '</strong> sera renommée');
			$editImage->addHidden('edit_image', $name);
			$editImage->addSubmit('Renommer');
		$editImage->closeFieldset();
	}
	// Ajouter une image
	$upload = new form(false, array('action' => rewrite('admin-images', array('pictures' => $_GET['pictures']))));
	$upload->openFieldset('Ajouter une image');
		$upload->addText('caption', 'Description');
		$upload->addFile('path', 'Chemin vers l\'image');
		$upload->addSubmit();
	$upload->closeFieldset();
	
	echo '<br />';
	if(isset($_GET['edit_image'])) echo $editImage;
	else echo $upload;
}
?>