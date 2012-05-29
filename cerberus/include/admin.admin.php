<?php
if(isset($_POST['edit']))
{
	foreach($_POST as $page => $droit)
		if($page != 'edit') $droits[$page] = $droit;

	$_POST['droits'] = json_encode($droits);
}
$langueAdmin = new admin();
$langueAdmin->setPage('cerberus_admin');
$langueAdmin->createList(array('Identifiant' => 'account'));
$langueAdmin->addOrEdit($diff, $diffText, $urlAction);

$return = NULL;
$return = a::simplify($langueAdmin->get('navigation'), $return);

if(isset($_GET['edit_admin']) or isset($_GET['add_admin']))
{
	$form = new form(false, array('action' => url::rewrite(NULL, $urlAction)));
	$form->getValues($langueAdmin->getFieldsTable());
	$select = new select();

	$pages = $langueAdmin->get('droits');
	$form->openFieldset('Pages autorisées');
		foreach($pages as $page => $state)
		{
			if(!empty($page))
			{
				$alias = l::get('menu-admin-'.$page, array_search($page, $return));
				if(is_numeric($alias)) $alias = $page;

				$select->newSelect($page, $alias);
				$select->appendList(array(1 => 'Oui', 0 => 'Non'), false);
				$val = ($state) ? 1 : 0;
				$select->setValue($val);
				$form->insertText($select);
			}
		}
	$form->addEdit();
	$form->addSubmit($diffText);
	$form->closeFieldset();
	echo $form;
}
