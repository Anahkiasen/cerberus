<?php
if(get('meta_structure'))
{
	$metaAdmin = new admin();
	$metaAdmin->setPage('cerberus_meta', array('titre', 'url', 'description'));

	// Si formulaire META
	if(isset($_POST['url']))
		unset($_POST);
}

// Sinon
if(isset($_POST['traduction_titre']))
{
	cache::delete('{meta-*,lang-*}');
	cache::delete($_POST['parent'].'-'.$_POST['page'], true);

	// Page actuelle
	$index = 'menu-'.$_POST['parent'].'-'.$_POST['page'];
	$already = db::field('cerberus_langue', 'tag', array('tag' => $index));
	if($already) db::update('cerberus_langue', array(l::admin_current() => $_POST['traduction_titre']), array('tag' => $index));
	else db::insert('cerberus_langue', array('tag' => $index, l::admin_current() => $_POST['traduction_titre']));
	
	// Page parente
	$index = 'menu-'.$_POST['parent'];
	$already = db::field('cerberus_langue', 'tag', array('tag' => $index));
	if($already) db::update('cerberus_langue', array(l::admin_current() => $_POST['traduction_parent_titre']), array('tag' => $index));
	else db::insert('cerberus_langue', array('tag' => $index, l::admin_current() => $_POST['traduction_parent_titre']));
}

$strucAdmin = new admin();
$strucAdmin->setPage('cerberus_structure', array('external_link'));

if(db::is_table('cerberus_meta'))
{
	$strucAdmin->addRow('meta', 'META');
	$strucAdmin->createList(
		array('index' => 'pageid', 'Titre' => l::admin_current(), 'Masqué' => 'hidden', 'En cache' => 'cache', 'Ordre' => 'page_priority'),
		array(
			'SELECT' => 'S.id AS id, S.hidden, S.cache, S.parent, S.page_priority, CONCAT_WS("-", S.parent, S.page) AS pageid, L.' .l::admin_current(). ', (SELECT ' .l::admin_current(). ' FROM cerberus_langue WHERE tag = CONCAT("menu-", parent)) AS categ',
			'FROM' => 'cerberus_meta M',
			'RIGHT JOIN' => 'cerberus_structure S ON S.id=M.page LEFT JOIN cerberus_langue L ON L.tag = CONCAT("menu-", CONCAT_WS("-", S.parent, S.page))',
			'ORDER BY' => 'S.parent_priority ASC, S.page_priority ASC',
			'WHERE' => 'S.parent IS NOT NULL',
		'DIVIDE' => 'categ'));
}
else
	$strucAdmin->createList(
		array('index' => 'pageid', 'Titre' => l::admin_current(), 'Masqué' => 'hidden', 'En cache' => 'cache', 'Ordre' => 'page_priority'),
		array(
			'SELECT' => 'S.id AS id, S.hidden, S.cache, S.parent, S.page_priority, CONCAT_WS("-", S.parent, S.page) AS pageid, L.' .l::admin_current(). ', (SELECT ' .l::admin_current(). ' FROM cerberus_langue WHERE tag = CONCAT("menu-", parent)) AS categ',
			'FROM' => 'cerberus_structure S',
			'LEFT JOIN' => 'cerberus_langue L ON L.tag = CONCAT("menu-", CONCAT_WS("-", S.parent, S.page))',
			'ORDER BY' => 'S.parent_priority ASC, S.page_priority ASC',
			'WHERE' => 'S.parent IS NOT NULL',
		'DIVIDE' => 'categ'));

// Formulaire META
if(isset($_GET['meta_structure']))
{
	$meta = 
		a::simple(db::join(
			'cerberus_meta M',
			'cerberus_structure S',
			'S.id = M.page', 'S.page, S.parent, M.id, M.page AS idx, M.titre, M.description, M.url',
			array('M.page' => $_GET['meta_structure'], 'M.langue' => l::admin_current())));

	$availablePages = a::simple(a::rearrange(db::select('cerberus_structure', 'id, CONCAT_WS("-", parent, page) AS page', NULL, 'parent_priority ASC, page_priority ASC'), 'id', TRUE));
	if(!isset($meta['id']))
	{
		$last = db::insert('cerberus_meta', array('page' => $_GET['meta_structure'], 'langue' => l::admin_current()));
		$meta = array('titre' => NULL, 'idx' => NULL, 'url' => NULL, 'description' => NULL);
		$_GET['edit_meta'] = $last;
	}
	else $_GET['edit_meta'] = $meta['id'];

	// Formulaire META
	$form = new form(false, array('action' => url::rewrite('admin-structure', array('meta_structure' => get('meta_structure')))));
	$select = new select();
	
	$form->getValues($metaAdmin->getFieldsTable());
	$titre = get('meta_structure') ? l::getalt('menu-'.$meta['parent'].'-'.$meta['page'], l::admin_current()) : NULL;
	$parent_titre = l::getalt('menu-'.$meta['parent'], l::admin_current());
	$form->addValue('traduction_titre', $titre);
	$form->addValue('traduction_parent_titre', $parent_titre);
	
	$form->openFieldset('Modifier des données meta');
		$select->newSelect('page', 'Identifiant de la page'); 
			$select->setValue($meta['idx']);
			$select->appendList($availablePages, false);
			$form->insertText($select);
			
		$form->addText('traduction_titre', 'Titre de la page');
		$form->addtext('traduction_parent_titre', 'Titre de la catégorie');
		$form->addText('titre', 'Balise &lt;title&gt; (7 à 10 mots, 100 caractères)', $meta['titre']);
		$form->addText('url', 'URL de la page (20 à 70 caractères)', $meta['url']);
		$form->addTextarea('description', 'Balise &lt;description&gt; (150 à 200 caractères)', $meta['description'], array('underfield' => true));
		$form->addEdit();
		$form->addSubmit('Modifier');
	$form->closeFieldset();
	
	echo $form;
}

// Formulaire STRUCTURE
$strucAdmin->addOrEdit($diff, $diffText, $urlAction);
if(isset($_GET['add_structure']) || isset($_GET['edit_structure']))
{				
	// Formulaire
	$form = new form(false, array('action' => url::rewrite('admin-structure', $urlAction)));
	$select = new select();
	$form->getValues($strucAdmin->getFieldsTable());
	
	$test = $form->passValues();	
	
	$form->openFieldset($diffText. ' l\'arobrescence');
		$form->addText('page', 'Identifiant de la page');
		$form->addText('parent', 'Identifiant de la catégorie');
	$form->closeFieldset();
	
	$form->openFieldset('Options');
		$form->addRadio('cache', array('Oui' => 1, 'Non' => 0), 'Autoriser la mise en cache');
		$form->addRadio('hidden', array('Oui' => 1, 'Non' => 0), 'Masquer dans les menus');
		$form->addText('page_priority', 'Ordre');
		$form->addText('external_link', 'Lien externe');
		
		$form->addEdit();
		$form->addSubmit($diffText);
	$form->closeFieldset();
	
	echo $form;
}
?>