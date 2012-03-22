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

<a href="<?= url::rewrite('admin-langue', array('bdd' => 'import')) ?>"><p id="left" class="btn"><i class="icon-signin"></i> Importer des traductions</p></a>
<a href="<?= url::rewrite('admin-langue', array('bdd' => 'export')) ?>"><p id="right" class="btn"><i class="icon-signout"></i> Exporter des traductions</p></a>
<p class="clear"></p>

<?php

$LANGUES = db::fields('cerberus_langue');
	unset($LANGUES[0]);

if(get('bdd'))
{
	// EXPORT
	if(get('bdd') == 'export')
	{
		$index = db::select('cerberus_langue', '*', NULL, 'tag ASC');
		a::csv($index, 'langues', implode(';', db::fields('cerberus_langue')));
		str::display('Le fichier a bien été crée, pour le télécharger ' .str::link('langues.csv', 'cliquez ici'));
	}
	else
	{
		if(isset($_FILES['import']['name']))
		{
			$filename = 'tmp.csv';
			$resultat = move_uploaded_file($_FILES['import']['tmp_name'], $filename);
			if($resultat)
			{
				$csv_content = file_get_contents($filename);
				$index = str::parse($csv_content, 'csv');
				foreach($index as $ligne => $colonnes)
				{
					if($ligne != 0 and count($colonnes) > 1)
						foreach($colonnes as $langue => $value)
							{
								$index_clean[$ligne][$langue] = substr($value, 1, -1);
							}
				}
				db::delete('cerberus_langue');
				db::insert_all('cerberus_langue', NULL, $index_clean);
				db::status('Fichier de langue correctement importé', 'Erreur lors de l\'import du fichier langue');
			}
		}
		else
		{
		?>
		<form enctype="multipart/form-data" method="post">
		<fieldset>
		<legend>Importer</legend>
		<dl class="file">
		<dt><label>Importer des traductions (CSV)</label></dt>
		<dd><input type="file" name="import" /><input type="submit" class="ok" value="ok" /></dd>
		</dl>
		</fieldset><p class="clear"></p></form>
		<?
		}
	}
}

$langueAdmin = new admin();
$langueAdmin->setPage('cerberus_langue');
$langueAdmin->createList(
	array_merge(array('Identifiant' => 'tag'), $LANGUES),
	array('WHERE' => 'tag NOT LIKE "menu-%"', 'ORDER BY' => 'tag ASC'));

$langueAdmin->addOrEdit($diff, $diffText, $urlAction);

// Formulaire
$forms = new forms();
$forms->values('cerberus_langue');

$forms->openFieldset($diffText. ' une traduction');
	$forms->addText('tag', 'Identitifant de la traduction');
	foreach(config::get('langues') as $langue)
		$forms->addText($langue, 'Traduction ' .strtoupper($langue));
	
	$forms->addType();
	$forms->addSubmit($diffText);
$forms->closeFieldset();
	
echo $langueAdmin->formAddOrEdit($forms->returns());
?>
