<?php
class switcher
{
	// Variantes et actuelle
	private $possible;
	private $actual;
	
	function __construct()
	{
		$this->possible = func_get_args();
		$sswitch = s::get('switch', $this->possible[0]);
		
		// Définition de la variante actuelle
		if(isset($sswitch)) $this->actual = $sswitch;
		if(isset($_GET['switch']) and in_array($_GET['switch'], $this->possible)) $this->actual = $_GET['switch'];
		s::set('switch', $this->actual);
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
		return 'pages/switch-'.$content.'.php';
	}
	
	function returnList()
	{
		return $this->possible;
	}
	
	function current()
	{
		return $this->actual;
	}
}

// Raccourci
function _s($page)
{
	global $switcher;
	return $switcher->content($page);
}
?>