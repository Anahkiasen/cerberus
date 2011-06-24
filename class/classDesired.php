<?php
class desired
{
	public $desired;
	
	private $optionMulti;
	private $optionSub;
	private $optionHTML;
	private $mode;
	
	private $page = 'home';
	private $subpage;
	private $subString;
	private $extension;
	
	private $renderNavigation;
	private $renderSubnav;
	
	function __construct($navigation, $optionHTML = FALSE)
	{
		global $index;
		
		$this->optionSub = (is_array($navigation[key($navigation)]));
		$this->optionMulti = (isset($index));
		
		// Définition du mode
		$this->mode .= ($this->optionMulti) ? 1 : 0;
		$this->mode .= ($this->optionSub) ? 1 : 0;
		
		// Page actuelle
		if(isset($_GET['page']))
		{
			$allowedPages = ($this->mode == 10) ? $navigation : array_keys($navigation);
			if(in_array($_GET['page'], $allowedPages)) $this->page = $_GET['page'];
		}
		if($this->optionSub)
		{
			$this->subpage = (isset($_GET['subpage']) && in_array($_GET['subpage'], $navigation[$this->page]))
				? $_GET['subpage']
				: $navigation[$this->page][0];
			$this->subString = '-' .$this->subpage;
		}
		
		// Inclusion de la page
		$filename = 'page-' .$this->page.$this->subString;
		if(file_exists('include/' .$filename. '.html')) $this->extension = '.html';
		elseif(file_exists('include/' .$filename. '.php')) $this->extension = '.php';
		else 
		{
			$this->page = 'home';
			$this->subString = '-home';
			$this->extension = '.php';
		}
		$filenameFinal = 'page-' .$this->page.$this->subString.$this->extension;
		
		// Rendu de la navigation
		$toMain = array_keys($navigation);
		if($this->mode == 11) $toSub = $navigation[$this->page];
		if($this->mode == 10) $toMain = $navigation;

		$this->renderNavigation = $this->formatLinks($toMain);
		$this->renderSubnav = $this->formatLinks($toSub, TRUE);
		
		$this->render(array($this->page, $this->subpage, $this->renderNavigation, $this->renderSubnav, $filenameFinal));
	}
	
	// Retourner la variable
	function render($array)
	{
		$this->desired = $array;
	}
	
	// Formater la navigation
	function formatLinks($links, $toSub = FALSE)
	{
		if($toSub == TRUE and $this->optionHTML == TRUE) $link = $this->page. '-';
		elseif($toSub == TRUE and $this->optionHTML == FALSE) $link = 'index.php?page=' .$this->page. '&subPage=';
		elseif($toSub == FALSE and $this->optionHTML == TRUE) $link = '';
		elseif($toSub == FALSE and $this->optionHTML == FALSE) $link = 'index.php?page=';
		$html = ($this->optionHTML == TRUE) ? '.html' : '';
		
		foreach($links as $key => $value)
			$keys[$key] = '<a href="' .$link.$value.$html. '">' .index('menu-' .$value). '</a>';
		
		return implode(' ', $keys);			
	}
}
?>