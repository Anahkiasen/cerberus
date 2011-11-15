<p>Ci-dessous il est possible de modifier les différentes traductions du site. À noter que toute modification aux noms des pages doivent être faites via la page <em>structure</em>.</p>

<?php
$langueAdmin = new AdminPage();
$langueAdmin->setPage('langue');
$langueAdmin->createList(
	array_merge(array('Identitifant' => 'tag'), config::get('langues')),
	array('WHERE' => 'tag NOT LIKE "menu-%"'));

$langueAdmin->addOrEdit($diff, $diffText, $urlAction);

// Formulaire
$form = new form(false);
$form->getValues($langueAdmin->getFieldsTable());

$form->openFieldset($diffText. ' une traduction');
	$form->addText('tag', 'Identitifant de la traduction');
	foreach(config::get('langues') as $langue)
	{
		$form->addText($langue, 'Traduction ' .strtoupper($langue));
	}
	$form->addEdit();
	$form->addSubmit($diffText);
$form->closeFieldset();
	
echo $langueAdmin->formAddOrEdit($form);
?>