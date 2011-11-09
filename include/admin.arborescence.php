<?php
$metaAdmin = new AdminPage();
$metaAdmin->setPage('structure');
$metaAdmin->createList(
	array('Page' => 'pageid'),
	array(
		'SELECT' => 'M.id AS id, S.parent, CONCAT_WS("-", S.parent, S.page) AS pageid',
		'FROM' => 'meta M',
		'LEFT JOIN' => 'structure S ON S.id=M.page',
		'ORDER BY' => 'S.parent_priority ASC, S.page_priority ASC',
		'DIVIDE' => 'parent'));
$metaAdmin->addOrEdit($diff, $diffText, $urlAction);
?>