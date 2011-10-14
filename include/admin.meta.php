<?php
$metaAdmin = new AdminPage();
$metaAdmin->setPage('meta', 'lien');
$metaAdmin->createList(array('page'));
$metaAdmin->addOrEdit($diff, $diffText, $urlAction);
// Formulaire
if(isset($_GET['add_meta']) || isset($_GET['edit_meta']))
{				
	global $navigation;
	
	// Liste des pages
	foreach($navigation as $key => $value)
		foreach($value as $page) $availablePages[] = $key. '-' .$page;

	// Formulaire
	$form = new form(false, array('action' => rewrite('admin-meta', $urlAction)));
	$select = new select();
	$form->getValues($metaAdmin->getFieldsTable());
	
	$form->openFieldset($diffText. ' des données meta');
		$select->newSelect('page', 'Identifiant de la page'); 
			$select->appendList($availablePages);
			$form->insertText($select);
		$form->addText('titre', 'Titre de la page');
		$form->addText('lien', 'URL de la page');
		$form->addTextarea('description', 'Description de la page', '', array('underfield' => true));
		$form->addEdit();
		$form->addSubmit($diffText);
	$form->closeFieldset();
	
	echo $form;
}
?>