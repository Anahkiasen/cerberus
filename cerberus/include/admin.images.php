<p>Depuis cette page vous pouvez décider de renommer toutes les images d'un dossier selon un format précis. En cliquant sur "Voir les images" vous pouvez aussi renommer/supprimer des images individuellement.</p>

<?php
use Cerberus\Admin\Admin,
    Cerberus\Modules\Form,
    Cerberus\Toolkit\Arrays as a,
    Cerberus\Toolkit\Database as db,
    Cerberus\Toolkit\File as f,
    Cerberus\Toolkit\Language as l,
    Cerberus\Toolkit\Request as r,
    Cerberus\Toolkit\String as str;

// Préfixe
if(isset($_POST['prefixpost'])) $_SESSION['prefix'] = $_POST['prefixpost'];
if(isset($_GET['noprefix'])) unset($_SESSION['prefix']);
$PREFIXE = a::get($_SESSION, 'prefix', NULL);

// Supprimer un dossier
if(isset($_GET['deleteFolder']))
{
	if(f::remove(PATH_FILE.$_GET['deleteFolder']. '/')) str::display('Le dossier a bien été supprimé', 'success');
	else str::display('Une erreur est survenue durant la suppression du dossier', 'error');
}

// Supprimer une image
if(isset($_GET['delete_image']))
{
	if(f::remove(PATH_FILE.$_GET['pictures']. '/' .$_GET['delete_image']))
		str::display('Image ' .$_GET['delete_image']. ' supprimée');
}

// Renommer une image
if(isset($_POST['oldfile']))
{
	$dossier = a::get(explode('/', $_POST['oldfile']), 2);
	$extension = f::extension($_POST['oldfile']);
	rename($_POST['oldfile'], PATH_FILE.$dossier. '/' .str::slugify($_POST['renommer']). '.jpg');
	str::display('Le fichier a bien été renommé', 'success');
}

// Renommer les images en masse
if(isset($_GET['rename']))
{
	$i = 0;
	$basename = $_GET['rename'];
	$glob = glob(PATH_FILE.$basename. '/*');

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
		rename($file, PATH_FILE.$basename. '/' .$newname);

		$i++;
	}

	str::display('Les images ont bien été renommées au format ' .$PREFIXE.$basename. '-XX');
}
?>

<div class="infoblock alert alert-info">
	Ajouter un préfixe au renommage automatique (ou <?= str::slink('admin-images', 'supprimer le préfixe enregistré', 'noprefix') ?>) :<br />
	<?php $form = new Form(array('class' => 'form-search'));
	$form->addText('prefixpost', 'Préfixe', $PREFIXE, array('style' => 'padding: 5px'));
	$form->addSubmit('ok');
	$form->render();
	?>
</div>

<table>
<thead>
	<tr>
		<th>Dossier</th>
		<th>Nombre d'images</th>
		<th>Renommer les images automatiquement</th>
		<th>Voir les images</th>
		<th>Supprimer</th>
	</tr>
</thead>
<tbody>
<?php
// Liste des dossiers d'images
foreach(glob(PATH_FILE. '*') as $file)
{
	if(is_dir($file) and !in_array(f::filename($file), array('cache', 'news')))
	{
		$basename = f::filename($file);
		$count = count(glob(PATH_FILE.$basename. '/*'));

		echo '
		<tr>
			<td>' .$basename. '</td>
			<td>' .$count. '</td>
			<td>' .str::slink(NULL, $PREFIXE.$basename.'-XX.jpg', array('rename' => $basename)). '</td>
			<td>' .str::slink(NULL, str::img(PATH_CERBERUS.'img/action-picture.png', 'Voir les images'), array('pictures' => $basename)). '</td>
			<td>' .str::slink(NULL, str::img(PATH_CERBERUS.'img/action-delete.png', 'Supprimer le dossier'), array('deleteFolder' => $basename)). '</td>
		</tr>';
	}
}
?>
</tbody>
</table>

<?php
// Afficher les images
if(isset($_GET['pictures']) and file_exists(PATH_FILE.$_GET['pictures']))
{
	$picpath = PATH_FILE.$_GET['pictures']. '/';

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
			<td>' .media::thumb('file/' .$_GET['pictures']. '/' .$basename, 150, 100). '</td>
			<td>' .$image. '</td>
			<td>
				<form method="post">
				<input type="text" name="renommer" style="width:70%" /><input type="submit" value="OK" style="width:30px; position: relative; top: -5px; left:-5px" />
				<input type="hidden" name="oldfile" value="' .$image. '" />
				</form>
				</td>
			<td>' .str::slink('admin-images', str::img(PATH_CERBERUS.'img/action-delete.png'), array('pictures' => $_GET['pictures'], 'delete_image' => $basename)). '</td>
		</tr>';
	}
	echo '</tbody></table>';

	// Renommer une image
	if(isset($_GET['edit_image']))
	{
		$name = str_replace('\\', '', $_GET['edit_image']);

		$editImage = new FormDeprecated(false);
		$editImage->openFieldset('Editer une image');
			$editImage->addText('Renommer', '<strong>' .$name. '</strong> sera renommée');
			$editImage->addHidden('edit_image', $name);
			$editImage->addSubmit('Renommer');
		$editImage->closeFieldset();
	}
	// Ajouter une image
	$upload = new FormDeprecated(false, array('action' => url::rewrite('admin-images', array('pictures' => $_GET['pictures']))));
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
