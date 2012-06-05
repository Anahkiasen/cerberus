<?php
$newsAdmin = new Admin();
$newsAdmin->setPage('cerberus_news');
$newsAdmin->createList(array('titre', 'date'));
$newsAdmin->addOrEdit($diff, $diffText, $urlAction);

// Vidage du cache
cache::delete('{news,actualite}', true);

// Formulaire
$forms = new forms();
$forms->values('cerberus_news');
$forms->openFieldset($diffText. ' une news');
{
	$forms->addText('titre', 'Titre de la news');
	$forms->addTextarea('contenu', 'Texte de la news');
	if(isset($_GET['edit_news']))
	{
		$path = $newsAdmin->getImage(get('edit_news'));
		if(file_exists($path) and !empty($path))
		{
			$forms->insert('
				<dl class="actualThumb">
				<dt>Supprimer la miniature actuelle</dt>
				<dd style="text-align:center"><p>' .media::thumb('file/'.$path, 125, 125, NULL, array('zc' => 1)). '<br />	'
					.str::slink(
					'admin-news',
					'Supprimer',
					array(
						'edit_news' => $_GET['edit_news'],
						'deleteThumb' => $_GET['edit_news'])).
				'</p></dd></dl>');
		}
	}
	$forms->addFile('thumb', 'Envoi d\'une miniature');
	$forms->addType();
	if($diffText == 'Ajouter') $forms->addHidden('date', date('Y-m-d'));
	$forms->addSubmit($diffText);
}
$forms->closeFieldset();

echo $newsAdmin->formAddOrEdit($forms->returns());
