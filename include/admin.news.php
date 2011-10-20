<?php
$newsAdmin = new AdminPage();
$newsAdmin->setPage('news');
$newsAdmin->createList(array('titre', 'date'));
$newsAdmin->addOrEdit($diff, $diffText, $urlAction);

// Formulaire
$form = new form(false, array('action' => rewrite('admin-news', $urlAction)));
$form->getValues($newsAdmin->getFieldsTable());

$form->openFieldset($diffText. ' une news');
	$form->addText('titre', 'Titre de la news');
	$form->addTextarea('contenu', 'Texte de la news');
	if(isset($_GET['edit_news']))
	{
		$path = $newsAdmin->getImage(get('edit_news'));
		if(file_exists('assets/file/news/' .$path) and !empty($path))
		{
			$form->insertText('
				<dl class="actualThumb">
				<dt>Supprimer la miniature actuelle</dt>
				<dd style="text-align:center"><p><img src="' .timthumb('news/' .$path, 125, 125, 1, false). '" /><br />	' 
					.str::slink(
					'admin-news',
					array(
						'edit_news' => $_GET['edit_news'],
						'deleteThumb' => $_GET['edit_news']),
					'Supprimer'). 
				'</p></dd></dl>');
		}
	}
	$form->addFile('thumb', 'Envoi d\'une miniature');
	$form->addEdit();
	if($diffText == 'Ajouter') $form->addHidden('date', date('Y-m-d')); 
	$form->addSubmit($diffText);
$form->closeFieldset();
	
echo $newsAdmin->formAddOrEdit($form);
?>