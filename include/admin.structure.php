<?php
if(get('meta_structure'))
{
	$metaAdmin = new AdminPage();
	$metaAdmin->setPage('meta');

	if(isset($_POST['lien']))
		unset($_POST);
}

if(isset($_POST['titre']))
{
	$index = 'menu-'.$_POST['parent'].'-'.$_POST['page'];
	$already = db::field('langue', $_SESSION['admin']['langue'], array('tag' => $index));
	if($already) db::update('langue', array($_SESSION['admin']['langue'] => $_POST['titre']), array('tag' => $index));
	else db::insert('langue', array('tag' => $index, $_SESSION['admin']['langue'] => $_POST['titre']));
}

$strucAdmin = new AdminPage();
$strucAdmin->setPage('structure');
$strucAdmin->addRow('meta', 'META');
$strucAdmin->createList(
	array('index' => 'pageid', 'Titre' => $_SESSION['admin']['langue'], 'Ordre' => 'page_priority'),
	array(
		'SELECT' => 'S.id AS id, S.parent, S.page_priority, CONCAT_WS("-", S.parent, S.page) AS pageid, L.' .$_SESSION['admin']['langue']. ', (SELECT ' .$_SESSION['admin']['langue']. ' FROM langue WHERE tag = CONCAT("menu-", parent)) AS categ',
		'FROM' => 'meta M',
		'LEFT JOIN' => 'structure S ON S.id=M.page LEFT JOIN langue L ON L.tag = CONCAT("menu-", CONCAT_WS("-", S.parent, S.page))',
		'ORDER BY' => 'S.parent_priority ASC, S.page_priority ASC',
		'WHERE' => 'S.parent IS NOT NULL',
		'DIVIDE' => 'categ'));

// Formulaire
if(isset($_GET['meta_structure']))
{
	global $navigation;

	$meta = a::simple(db::left_join('meta M', 'structure S', 'S.id = M.page', 'M.id, M.page AS idx, M.titre, M.description, M.url', array('M.page' => $_GET['meta_structure'])));
	$availablePages = a::simple(a::rearrange(db::select('structure', 'id, CONCAT_WS("-", parent, page) AS page', NULL, 'parent_priority ASC, page_priority ASC'), 'id', TRUE));
	$_GET['edit_meta'] = $meta['id'];

	// Formulaire
	$form = new form(false, array('action' => rewrite('admin-structure', array('meta_structure' => get('meta_structure')))));
	$select = new select();
	$form->getValues($metaAdmin->getFieldsTable());
	
	$form->openFieldset('Modifier des données meta');
		$select->newSelect('page', 'Identifiant de la page'); 
			$select->setValue($meta['idx']);
			$select->appendList($availablePages, false);
			$form->insertText($select);
		$form->addText('titre', 'Titre de la page', $meta['titre']);
		$form->addText('lien', 'URL de la page', $meta['url']);
		$form->addTextarea('description', 'Description de la page', $meta['description'], array('underfield' => true));
		$form->addEdit();
		$form->addSubmit('Modifier');
	$form->closeFieldset();
	
	echo $form;
}

// Formulaire
$strucAdmin->addOrEdit($diff, $diffText, $urlAction);
if(isset($_GET['add_structure']) || isset($_GET['edit_structure']))
{				
	global $navigation;
	
	// Liste des pages
	foreach($navigation as $key => $value)
		foreach($value as $page) $availablePages[] = $key. '-' .$page;

	// Formulaire
	$form = new form(false, array('action' => rewrite('admin-structure', $urlAction)));
	$select = new select();
	$form->getValues($strucAdmin->getFieldsTable());
	
	$test = $form->passValues();
	$titre = get('edit_structure') ? l::get('menu-'.$test['parent'].'-'.$test['page'], NULL) : NULL;
	$form->addValue('titre', $titre);
	
	$form->openFieldset($diffText. ' l\'arobrescence');
		$form->addText('page', 'Identifiant de la page');
		$form->addText('parent', 'Page parente');
		$form->addText('titre');
		$form->addText('page_priority', 'Ordre');
		
		$form->addEdit();
		$form->addSubmit($diffText);
	$form->closeFieldset();
	
	echo $form;
}
?>