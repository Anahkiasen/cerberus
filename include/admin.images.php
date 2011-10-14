<h1>Liste des dossiers d'images</h1>

<?php
// Préfixe
if(isset($_POST['prefixpost'])) $_SESSION['prefix'] = $_POST['prefixpost'];
if(isset($_GET['noprefix'])) unset($_SESSION['prefix']);
$PREFIXE = (isset($_SESSION['prefix'])) ? $_SESSION['prefix'] : NULL;

// Supprimer un dossier
if(isset($_GET['deleteFolder']))
{
	if(sunlink('assets/file/' .$_GET['deleteFolder']. '/')) echo display('Le dossier a bien été supprimé');
	else echo display('Une erreur est survenue durant la suppression du dossier');
}

// Renommer une image
if(isset($_POST['oldfile']))
{
	$explode = explode('/', $_POST['oldfile']);
	$dossier = $explode[2];
	$extension = pathinfo($_POST['oldfile'], PATHINFO_EXTENSION);
	rename($_POST['oldfile'], 'assets/file/' .$dossier. '/' .normalize($_POST['renommer']). '.jpg');
	echo display('Le fichier a bien été renommé');
}

// Renommer les images
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
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		$newname = $PREFIXE.$basename. '-' .str_pad($i, $numpad, "0", STR_PAD_LEFT). '.' .$extension;
		rename($file, 'assets/file/' .$basename. '/' .$newname);
		
		$i++;
	}
	
	echo display('Les images ont bien été renommées au format ' .$PREFIXE.$basename. '-XX');
}
?>

<form method="post" action="<?= rewrite('admin-images') ?>">
<p>Ajouter un préfixe au renommage automatique (ou <a href="<?= rewrite('admin-images', 'noprefix') ?>">supprimer le préfixe enregistré</a>) :<br />
<input type="text" name="prefixpost" value="<?= $PREFIXE ?>" /> <input type="submit" value="OK" style="width: 30px; position:relative; left:-10px; top: -5px" />
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
	if(is_dir($file) and !in_array(basename($file), array('cache', 'news')))
	{
		$basename = basename($file);
		$count = count(glob('assets/file/' .$basename. '/*'));
	
		echo '
		<tr>
			<td>' .$basename. '</td>
			<td>' .$count. '</td>
			<td><a href="' .rewrite('admin-images', array('rename' => $basename)). '">' .$PREFIXE.$basename. '-XX.jpg</a></td>
			<td><a href="' .rewrite('admin-images', array('pictures' => $basename)). '"><img src="assets/css/picture.png" alt="Voir les images" /></a></td>
			<td><a href="' .rewrite('admin-images', array('deleteFolder' => $basename)). '"><img src="assets/css/delete.png" alt="Supprimer le dossier" /></a></td>
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
		$basename = basename($image);
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
			<td><a href="' .rewrite('admin-images', array('pictures' => $_GET['pictures'], 'delete_image' => $basename)). '"><img src="assets/css/delete.png" /></a></td>
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