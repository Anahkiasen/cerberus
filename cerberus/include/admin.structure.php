<?php
if(get('meta_structure'))
{
	$metaAdmin = new Admin();
	$metaAdmin->setPage('cerberus_meta', array('titre', 'url', 'description'));

	// Sinon
	if(r::post('traduction_titre'))
	{
		$page = db::row('cerberus_structure', '*', array('id' => r::post('page')));
		$_POST['parent'] = $page['parent'];
		$_POST['page'] = $page['page'];

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

	// Si formulaire META
	if(isset($_POST['url']))
		unset($_POST);
}

if(r::post('page_priority'))
{
	$idOld = get('edit_structure');
	$old = db::row('cerberus_structure', '*', array('id' => $idOld));

	// Changement du nom des fichiers
	$page = !empty($old['page']) ? $old['parent'].'-'.$old['page'] : $old['parent'];
	$newName = !empty($_POST['page']) ? r::post('parent').'-'.r::post('page') : r::post('parent');
	$pages = glob('pages/' .$page. '.{html,php}', GLOB_BRACE);
	if(sizeof($pages) == 1) f::rename($pages[0], $newName);

	// Nom des éléments de traduction
	db::update('cerberus_langue', array('tag' => 'menu-'.$newName), array('tag' => 'menu-'.$page));
	db::last_sql();
}

$strucAdmin = new Admin();
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
		a::simplify(db::join(
			'cerberus_meta M',
			'cerberus_structure S',
			'S.id = M.page', 'S.page, S.parent, M.id, M.page AS idx, M.titre, M.description, M.url',
			array('M.page' => $_GET['meta_structure'], 'M.langue' => l::admin_current())));

	$availablePages = a::simplify(a::rearrange(db::select('cerberus_structure', 'id, CONCAT_WS("-", parent, page) AS page', NULL, 'parent_priority ASC, page_priority ASC'), 'id', TRUE));
	if(!isset($meta['id']))
	{
		$last = db::insert('cerberus_meta', array('page' => $_GET['meta_structure'], 'langue' => l::admin_current()));
		$meta = array('titre' => NULL, 'idx' => NULL, 'url' => NULL, 'description' => NULL);
		$_GET['edit_meta'] = $last;
	}
	else $_GET['edit_meta'] = $meta['id'];

	// Formulaire META
	$form = new forms(array('action' => url::reload(array('meta_structure' => get('meta_structure')))));
	$form->values('cerberus_meta');
	$titre = get('meta_structure') ? l::getalt('menu-'.$meta['parent'].'-'.$meta['page'], l::admin_current()) : NULL;
	$parentTitre = l::getalt('menu-'.$meta['parent'], l::admin_current());

	$form->setValue('traduction_titre', $titre);
	$form->setValue('traduction_parent_titre', $parentTitre);

	$form->openFieldset('Modifier des données meta');
		$form->addSelect('page', 'Identifiant de la page', $availablePages, $meta['idx'], array('force_index' => true));
		$form->addText('traduction_titre', 'Titre de la page');
		$form->addtext('traduction_parent_titre', 'Titre de la catégorie');
		$form->addText('titre', 'Balise &lt;title&gt; (7 à 10 mots, 100 caractères)', $meta['titre']);
		$form->addText('url', 'URL de la page (20 à 70 caractères)', $meta['url']);
		$form->addTextarea('description', 'Balise &lt;description&gt; (150 à 200 caractères)', $meta['description']);
		$form->addType();
		$form->addSubmit('Modifier');
	$form->closeFieldset();
	$form->render();
}

// Formulaire STRUCTURE
$strucAdmin->addOrEdit($diff, $diffText, $urlAction);
if(isset($_GET['add_structure']) || isset($_GET['edit_structure']))
{
	$form = new forms(array('action' => url::reload($urlAction)));
	$form->values('cerberus_structure');
	$form->openFieldset($diffText. ' l\'arborescence');
		$form->addText('page', 'Identifiant de la page');
		$form->addText('parent', 'Identifiant de la catégorie');
	$form->closeFieldset();

	$form->openFieldset('Options');
		$form->addRadio('cache', 'Autoriser la mise en cache', array(1 => 'Oui', 0 => 'Non'));
		$form->addRadio('hidden', 'Masquer dans les menus', array(1 => 'Oui', 0 => 'Non'));
		$form->addText('page_priority', 'Ordre');
		$form->addText('external_link', 'Lien externe');

		$form->addType();
		$form->addSubmit($diffText);
	$form->closeFieldset();
	$form->render();
}
