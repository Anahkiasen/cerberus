<?php
class switcher
{
	// Variantes et actuelle
	private $possible;
	private $actual;
	
	function __construct()
	{
		$this->possible = func_get_args();
		
		// Définition de la variante actuelle
		if(isset($_SESSION['switch'])) $this->actual = $_SESSION['switch'];
		if(!isset($this->actual)) $this->actual = $this->possible[0];
		if(isset($_GET['switch']) and in_array($_GET['switch'], $this->possible)) $this->actual = $_GET['switch'];
		$_SESSION['switch'] = $this->actual;
	}
	
	// Obtenir le chemin actuel
	function path($getFolder = 'all')
	{
		$path = 'assets/switch/' .$this->actual. '/';
		switch($getFolder)
		{
			case 'css':
				return $path. 'css/';
				break;
			case 'js':
				return $path. 'js/';
				break;
			case 'php':
				return $path. 'php/';
				break;
			
			default:
				return $path;
				break;
		}
	}
	
	// Récupération du contenu
	function content($content)
	{
		if(file_exists($this->path('php').$content.'.php')) return $this->path('php').$content.'.php';
		elseif(file_exists('pages/switch-'.$content.'.php')) return 'pages/switch-'.$content.'.php';
		else echo display('Bloc [' .$content. '] non trouvé');
	}
	
	function returnList()
	{
		return $this->possible;
	}
}

// Raccourci
function __($page)
{
	global $switcher;
	return $switcher->content($page);
}
?>