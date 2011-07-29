<?php
/*
	Classe desiredPage
	# Détermine la page en cours et construit les menus à partir d'un arbre de navigation
	
	$navigation
		Arbre de navigation du site en cours au format correspondant
		selon si le site dispose ou non d'une sous-navigation, et/ou
		est multilangue ou non
	$isRewrite
		Active ou non la réecriture d'URL
*/
class desiredPage
{
	public $return;
	
	private $isMultilangue;
	private $isSubnav;
	private $isRewrite;
	private $options;
	
	private $page = 'home';
	private $pageSub;
	private $pageExtension;
	private $pageSub_string;
	
	private $navigationTree;
	private $renderNavigation;
	private $renderSubnav;
	
	function __construct($navigation, $isRewrite = FALSE, $isListed = FALSE)
	{
		global $index;
		
		$this->navigationTree = $navigation;
		$this->isRewrite = $isRewrite;
		$this->isSubnav = (is_array($navigation[key($navigation)]));
		$this->isMultilangue = (isset($index));
		
		// Définition du mode
		$this->options .= ($this->isMultilangue) ? 1 : 0;
		$this->options .= ($this->isSubnav) ? 1 : 0;
		
		// Page actuelle
		if(isset($_GET['page']))
		{
			$allowedPages = ($this->options == 10) ? $navigation : array_keys($navigation);
			if(in_array($_GET['page'], $allowedPages)) $this->page = $_GET['page'];
		}
		if($this->isSubnav)
		{
			$this->pageSub = (isset($_GET['pageSub']) && in_array($_GET['pageSub'], $navigation[$this->page]))
				? $_GET['pageSub']
				: $navigation[$this->page][0];
			$this->pageSub_string = '-' .$this->pageSub;
		}
		
		// Inclusion de la page
		$filename = '' .$this->page.$this->pageSub_string;
		if(file_exists('pages/' .$filename. '.html')) $this->pageExtension = '.html';
		elseif(file_exists('pages/' .$filename. '.php')) $this->pageExtension = '.php';
		else 
		{
			$this->page = 'home';
			$this->pageSub_string = '-home';
			$this->pageExtension = '.php';
		}
		$filenameFinal = $this->page.$this->pageSub_string.$this->pageExtension;
		
		// Rendu de la navigation
		$toMain = array_keys($navigation);
		if($this->options == 11) $toSub = $navigation[$this->page];
		if($this->options == 10) $toMain = $navigation;

		$this->renderNavigation = $this->formatLinks($toMain, $isListed);
		$this->renderSubnav = $this->formatLinks($toSub, FALSE, TRUE);
		
		$this->return = array($this->page, $this->pageSub, $this->renderNavigation, $this->renderSubnav, $filenameFinal);
	}
			
	// Formater la navigation
	function formatLinks($links, $listed = FALSE, $toSub = FALSE)
	{
		if($toSub == TRUE and $this->isRewrite == TRUE) $link = $this->page. '-';
		elseif($toSub == TRUE and $this->isRewrite == FALSE) $link = 'index.php?page=' .$this->page. '&pageSub=';
		elseif($toSub == FALSE and $this->isRewrite == TRUE) $link = '';
		elseif($toSub == FALSE and $this->isRewrite == FALSE) $link = 'index.php?page=';
		
		// Hover
		$html = ($this->isRewrite == TRUE) ? '.html' : '';
		$hoverReference = ($toSub == TRUE) ? $this->pageSub : $this->page;
	
		$indexSub = ($toSub == TRUE) ? $this->page. '-' : '';
		
		foreach($links as $key => $value)
		{
			$hover = ($value == $hoverReference) ? ' class="hover"' : '';
			$href = $link.$value.$html;
			$linkName = ($this->isMultilangue == TRUE) ? index('menu-' .$indexSub.$value) : $value;
			if($listed == TRUE) $linkName = '<li' .$hover. '>' .$linkName. '</li>';
			$keys[$key] = '<a href="' .$href. '"' .$hover. '>' .$linkName. '</a>';
		}
		
		if($listed == TRUE) return '<ul>' .implode('', $keys). '</ul>';
		else return implode(' ', $keys);			
	}
}
?>