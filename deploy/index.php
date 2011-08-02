<?php
if(!file_exists('../../index.php'))
{
	// Fonctions moteur
	function copydir($source, $destination)
	{
		$dir = opendir($source); 
		@mkdir($destination); 
		
		while(false !== ($file = readdir($dir)))
		{
			if(($file != '.') && ($file != '..')) 
			{
				if(is_dir($source. '/' .$file)) copydir($source. '/' . $file, $destination. '/' .$file); 
				else copy($source. '/' .$file, $destination. '/' .$file); 
			} 
		} 
		
		closedir($dir); 
	} 
	include('../tools/sfputs.php');
	
	// Création des dossiers
	mkdir('../../css/');
	mkdir('../../pages/');
	mkdir('../../file/');
	mkdir('../cache/');

	// Déplacement des fichiers CSS et PHP
	copy('styles.css', '../../css/styles.css');
	copy('cerberus.css', '../../css/cerberus.css');
	copydir('overlay', '../../css/overlay');
	copy('timthumb.php', '../../file/timthumb.php');
	copy('main.php', '../../index.php');
	copy('../../n.htaccess', 'n.htaccess');
	
	echo 'Cerberus correctement d&eacute;ploy&eacute;';
}
else echo 'Cerberus d&eacute;j&agrave; d&eacute;ploy&eacute;';
?>