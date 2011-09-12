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
	private $optionRewrite = TRUE;
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
		if($_SERVER['HTTP_HOST'] == 'localhost:8888') $this->optionRewrite = false;
		
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
		else $pageSubString = '';
		
		// Include de la page
		$filename = $this->page.$pageSubString;
		if(file_exists('pages/' .$filename. '.html')) $pageExtension = '.html';
		elseif(file_exists('pages/' .$filename. '.php')) $pageExtension = '.php';
		else 
		{
			$this->page = $this->allowedPages[0];
			if($this->optionSubnav)
			{
				$this->pageSub = $navigation[$this->page][0];
			 	$pageSubString = '-' .$this->pageSub;
			}
			$pageExtension = (file_exists('pages/' .$this->page.$pageSubString. '.php'))
				? '.php'
				: '.html';
		}
		$this->filePath = $this->page.$pageSubString.$pageExtension;
	}
	function setRewrite($isRewrite)
	{
		$this->optionRewrite = $isRewrite;
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
				if($key != 'admin' or ($key == 'admin' and $_SERVER['HTTP_HOST'] == 'localhost:8888'))
					$this->treeNavigation[$key] = rewrite($key, array('subnav' => $this->optionSubnav));			
		
		if(!isset($this->treeSubnav) and $this->optionSubnav)
			foreach($this->cacheTree[$this->page] as $key)
			$this->treeSubnav[$key] = rewrite(array($this->page, $key), array('subnav' => $this->optionSubnav));			
	}
	
	// Altération des liens de la liste
	function alterTree($key, $newLink = '', $subTree = false)
	{
		$this->createTree();
		
		if(findString('-', $key))
		{
			$key = explode('-', $key);
			$sup = $key[0];
			$key = $key[1];
			$subTree = true;
		}
		
		$thisTree = ($subTree == true) ? 'treeSubnav' : 'treeNavigation';
		if(!empty($newLink))
		{
			if(isset($this->{$thisTree}[$key])) $this->{$thisTree}[$key] = $newLink;
		}
		else unset($this->{$thisTree}[$key]);
	}
	
	function render($glue = '')
	{
		$this->createTree();
		if($_SERVER['HTTP_HOST'] != 'localhost:8888') unset($this->treeNavigation['admin']);

		// Navigation principale
		foreach($this->treeNavigation as $key => $value)
		{
			$linkText = ($this->optionMultilangue) ? index('menu-' .$key) : $this->cacheTree[$key];			
			$hover = ($key == $this->page) ? ' class="hover"' : '';
			$keys[] = ($this->optionListed)
				? '<li' .$hover.'><a href="' .$value. '">' .$linkText. '</a></li>'
				: '<a href="' .$value. '"' .$hover. '>' .$linkText. '</a>';
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
		else $this->renderSubnav = '';

		return array($this->page, $this->pageSub, $this->renderNavigation, $this->renderSubnav, $this->filePath);
	}
}
?>