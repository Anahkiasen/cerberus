<p>Ci-dessous il est possible de modifier les différentes traductions du site. À noter que toute modification aux noms des pages doivent être faites via la page <em>structure</em>.</p>

<style type="text/css">
.admin-langue table td.tablerow-data
{
	text-align:left !important; 
	width: 30%;
}
.admin-langue table td:first-child { width: 25%; }
.admin-langue a p.infoblock { text-align:center !important; }
</style>

<a href="<?= rewrite('admin-langue', array('bdd' => 'import')) ?>"><p id="left" class="infoblock">Importer des traductions</p></a>
<a href="<?= rewrite('admin-langue', array('bdd' => 'export')) ?>"><p id="right" class="infoblock">Exporter des traductions</p></a>
<p class="clear"></p>

<?php

$LANGUES = db::fields('langue');
	unset($LANGUES[0]);

if(get('bdd'))
{
	// EXPORT
	if(get('bdd') == 'export')
	{
		$index = db::select('langue', '*', NULL, 'tag ASC');
		a::csv($index, 'langues', implode(';', db::fields('langue')));
		prompt('Le fichier a bien été crée, pour le télécharger ' .str::link('langues.csv', 'cliquez ici'));
	}
}

$langueAdmin = new AdminPage();
$langueAdmin->setPage('langue');
$langueAdmin->createList(
	array_merge(array('Identifiant' => 'tag'), $LANGUES),
	array('WHERE' => 'tag NOT LIKE "menu-%"', 'ORDER BY' => 'tag ASC'));

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
