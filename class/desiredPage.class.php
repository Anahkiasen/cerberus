<?php
/*
	Classe desiredPage
	# Détermine la page en cours et construit les menus à partir d'un arbre de navigation
	
	$navigation
		Arbre de navigation du site en cours au format correspondant
		selon si le site dispose ou non d'une sous-navigation, et/ou
		est multilangue ou non
*/
class desiredPage
{
	// Options de fonctionnement
	private $optionMultilangue = TRUE;
	private $optionSubnav = TRUE;
	private $optionMono = FALSE;
	
	private $optionListed = FALSE;
	private $optionListedSub = FALSE;
	
	private $options;

	// Paramètres
	private $page = 'home';
	private $pageSub;
	private $allowedPages;

	// Caches
	private $cacheTree;
	private $treeNavigation;
	private $treeSubnav;

	// Rendus
	private $renderNavigation;
	private $renderSubnav;
	
	// Fonctions moteur
	function __construct($navigation)
	{
		global $index;
		
		if(empty($navigation)) $navigation = array('home' => array('home'));
		
		// Définition du mode
		$this->cacheTree = $navigation;
		$this->allowedPages = array_keys($navigation);
		$this->optionSubnav = (is_array($navigation[key($navigation)]));
		$this->optionMultilangue = (isset($index));
		$this->options = boolprint($this->optionMultilangue).boolprint($this->optionSubnav);
		
		// Page par défaut
		$this->page = $this->allowedPages[0];
		
		// Page actuelle
		if(isset($_GET['page']))
		{
			if($this->options == 'TRUEFALSE') $this->allowedPages = $navigation; // TRUEFALSE est le seul cas où $allowed n'est pas $key
			if(in_array($_GET['page'], $this->allowedPages)) $this->page = $_GET['page'];
		}
		if($this->optionSubnav and !empty($navigation[$this->page]))
		{
			$this->pageSub = (isset($_GET['pageSub']) && in_array($_GET['pageSub'], $navigation[$this->page]))
				? $_GET['pageSub']
				: $navigation[$this->page][0];
			$pageSubString = '-' .$this->pageSub;
		}
		else $pageSubString = NULL;
		
		// Include de la page
		$filename = $this->page.$pageSubString;
		$fileExists = $this->fileExists($filename);
		if(!$fileExists)
		{
			$this->page = $this->allowedPages[0];
			if($this->optionSubnav)
			{
				$this->pageSub = $navigation[$this->page][0];
			 	$pageSubString = '-' .$this->pageSub;
			}
			$fileExists = $this->fileExists($this->page.$pageSubString);
		}
		
		$this->filePath = $this->page.$pageSubString.$fileExists;
	}
	function set($variable, $value)
	{
		$this->{$variable} = $value;
	}
	function setListed($isListed, $isListedSub)
	{
		$this->optionListed = $isListed;
		$this->optionListedSub = $isListedSub;
	}
	
	// Création des arrays de liens
	function createTree()
	{		
		if(!isset($this->treeNavigation))
			foreach($this->allowedPages as $key)
				if($key != 'admin' or ($key == 'admin' and $GLOBALS['cerberus']->isLocal()))
					$this->treeNavigation[$key] = rewrite($key, array('subnav' => $this->optionSubnav));			
		
		if(!isset($this->treeSubnav) and $this->optionSubnav)
			foreach($this->cacheTree[$this->page] as $key)
			$this->treeSubnav[$key] = rewrite(array($this->page, $key), array('subnav' => $this->optionSubnav));			
	}
	
	// Altération des liens de la liste
	function alterTree($key, $newLink = NULL, $subTree = false)
	{
		$this->createTree();
		
		if(findString('-', $key))
		{
			$key = explode('-', $key);
			$sup = $key[0];
			$key = $key[1];
			$subTree = true;
		}
		
		$thisTree = ($subTree) ? 'treeSubnav' : 'treeNavigation';
		if(!empty($newLink))
		{
			if(isset($this->{$thisTree}[$key])) $this->{$thisTree}[$key] = $newLink;
		}
		else unset($this->{$thisTree}[$key]);
	}
	
	function render($glue = NULL)
	{		
		$this->createTree();
		if(!$GLOBALS['cerberus']->isLocal()) unset($this->treeNavigation['admin']);

		// Navigation principale
		foreach($this->treeNavigation as $key => $value)
		{
			$linkText = ($this->optionMultilangue) ? index('menu-' .$key) : $this->cacheTree[$key];			
			$hover = ($key == $this->page) ? ' hover' : '';
			$mono = (($this->optionMono or $value == 'mono') and $key != 'admin')
				? 'id="mono-' .$key. '" rel="mono"'
				: 'href="' .$value. '"';
			$keys[] = ($this->optionListed)
				? '<li class="menu-' .$key. ' ' .$hover.'"><a ' .$mono. '>' .$linkText. '</a></li>'
				: '<a ' .$mono. ' class="menu-' .$key. ' ' .$hover.'">' .$linkText. '</a>';
		}
		$this->renderNavigation = ($this->optionListed)
			? '<ul>' .implode($glue, $keys). '</ul>'
			: implode($glue, $keys);
			unset($keys);
		
		// Sous-navigation
		if(!empty($this->treeSubnav))
		{
			foreach($this->treeSubnav as $key => $value)
			{
				$linkText = ($this->optionMultilangue) ? index('menu-' .$this->page. '-' .$key) : $this->cacheTree[$key];						
				$hover = ($key == $this->pageSub) ? ' class="hover"' : '';
				$keys[] = ($this->optionListedSub)
					? '<li' .$hover.'><a href="' .$value. '">' .$linkText. '</a></li>'
					: '<a href="' .$value. '"' .$hover. '>' .$linkText. '</a>';
			}
			$this->renderSubnav = ($this->optionListedSub)
				? '<ul>' .implode($glue, $keys). '</ul>'
				: implode($glue, $keys);
		}
		else $this->renderSubnav = NULL;

		return array($this->page, $this->pageSub, $this->renderNavigation, $this->renderSubnav, $this->filePath);
	}
	
	// Génération du contenu
	function content()
	{
		/*
		if($this->optionMono and $this->page != 'admin')
		{
			foreach($this->cacheTree as $key => $value)
			{
				foreach($value as $subkey => $subval)
				{
					$page = $key. '-' .$subval;
					$extension = $this->fileExists($page);
					if($extension)
					{
						echo '<div class="mono" id="content-' .$page. '">';
						include('pages/' .$page.$extension);
						echo '</div>';
					}
				}
			}
		}
		else
		{
			if(file_exists('pages/' .$this->filePath)) include_once('pages/' .$this->filePath);
			else echo display('Une erreur est survenue lors du chargement de la page');
		}
		*/
		
		global $switcher;
		
		if(isset($switcher) and file_exists($switcher->path('php').$this->filePath)) include_once $switcher->path('php').$this->filePath;
		elseif(file_exists('pages/' .$this->filePath)) include_once('pages/' .$this->filePath);
		else echo display('Une erreur est survenue lors du chargement de la page');
	}
	
	function fileExists($page)
	{
		if(file_exists('pages/' .$page. '.html')) return '.html';
		elseif(file_exists('pages/' .$page. '.php')) return '.php';
		else 
		{
			return FALSE;
			echo display('Une erreur est survenue lors du chargement de la page');
		}
	}
}
?>