<?php
if(isset($_POST['edit']))
{
	foreach($_POST as $page => $droit)
		if($page != 'edit') $droits[$page] = $droit;
		
	$_POST['droits'] = json_encode($droits);
}
$langueAdmin = new AdminPage();
$langueAdmin->setPage('cerberus_admin');
$langueAdmin->createList(array('Identifiant' => 'account'));
$langueAdmin->addOrEdit($diff, $diffText, $urlAction);

if(isset($_GET['edit_admin']) or isset($_GET['add_admin']))
{
	$form = new form(false, array('action' => rewrite(NULL, $urlAction)));
	$form->getValues($langueAdmin->getFieldsTable());
	$select = new select();
	
	$pages = $langueAdmin->get('droits');
	$form->openFieldset('Pages autorisées');
		foreach($pages as $page => $state)
		{
			if(!empty($page))
			{
				$select->newSelect($page);
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
?>