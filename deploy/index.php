<?php
if(!file_exists('../index.php'))
{
	include('tools/sfputs.php');
	
	mkdir('../css/');
	mkdir('../pages/');
	mkdir('cache/');

	copy('styles.css', '../css/styles.css');
	copy('cerberus.css', '../css/cerberus.css');
	copy('main.php', '../index.php');
	copy('overlay/', '../css/overlay/');

	sfputs('../n.htaccess', 'FileETag none');
	sfputs('../index.php', $indexFile);
}
else echo 'Cerberus déjà déployé';
?>