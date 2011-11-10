<?php
/*
	Classe Navigation
	# Détermine la page en cours et construit les menus à partir d'un arbre de navigation
	
	$navigation
		Arbre de navigation du site en cours au format correspondant
		selon si le site dispose ou non d'une sous-navigation, et/ou
		est multilangue ou non
*/
class navigation
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
	private $navigation;
	private $treeNavigation;
	private $treeSubnav;

	// Rendus
	private $renderNavigation;
	private $renderSubnav;
	
	/*
	########################################
	############## MISE EN PLACE ###########
	######################################## 
	*/
	
	// Fonctions moteur
	function __construct($navigation = NULL)
	{
		if(!$navigation and db::is_table('structure'))
		{
			$navbis = db::select('structure', 'page, parent', NULL, 'parent_priority ASC, page_priority ASC');
			foreach($navbis as $values) $navigation[$values['parent']][] = $values['page'];
		}
		elseif($navigation and !db::is_table('structure')) $this->createStructure($navigation);
	
		// Navigation par défaut
		if(!isset($navigation) or empty($navigation))
			 $navigation = array(
			 	'home' => array('home'),
				'admin' => array('admin'));
		
		// Options et modes
		$allowed_pages = array_keys($navigation);
		$pageSub = NULL;
		$page = (isset($_GET['404'])) ? '404' : $allowed_pages[0];
		$this->optionSubnav = (isset($navigation[$page]) and is_array($navigation[$page]));
		$this->options = boolprint(MULTILANGUE).boolprint($this->optionSubnav);
		
		// Page actuelle
		if(get('page'))
		{
			if($this->options == 'TRUEFALSE') $allowed_pages = $navigation; // MULTILINGUE SANS ARBO est un cas où $allowed_pages n'est pas $keys
			if(in_array(get('page'), $allowed_pages)) $page = get('page');
		}
		
		// Sous-navigation
		if($this->optionSubnav and isset($navigation[$page]) and !empty($navigation[$page]))
		{
			$substring = '-';
			$pageSub = (get('pageSub') and in_array(get('pageSub'), $navigation[$page]))
				? get('pageSub')
				: $navigation[$page][0];
		}
		else $substring = NULL;
		
		// Include de la page
		if($page != 'admin')
		{
			$extension = $this->extension($page.$substring.$pageSub);
			if(!$extension)
			{
				$page = $allowed_pages[0];
				if($this->optionSubnav)
				{
					$substring = '-';
					$pageSub = $navigation[$page][0];
				}
				$extension = $this->extension($page.$substring.$pageSub);
			}
			$this->filepath = $page.$substring.$pageSub.$extension;
		}
		else if(get('admin')) $pageSub = get('admin');
		
		// Enregistrement des variables
		$this->navigation = $navigation;
		$this->page = $page;
		$this->pageSub = $pageSub;
		$this->pageDATA = db::row('structure', 'id,cache', array('parent' => $this->page, 'page' => $this->pageSub));
		$this->allowedPages = $allowed_pages;
	}
	
	// Vérification de l'existence d'une page
	function extension($page)
	{
		if(file_exists('pages/' .$page. '.html')) return '.html';
		elseif(file_exists('pages/' .$page. '.php')) return '.php';
		else 
		{
			return FALSE;
			prompt('Une erreur est survenue lors du chargement de la page');
		}
	}
	
	// Afficher les menus en ligne ou en liste
	function listed($menu = FALSE, $submenu = FALSE)
	{
		$this->optionListed = $menu;
		$this->optionListedSub = $submenu;
	}
	
	// Vérifie la présence d'une clé dans l'arbre
	function get($key = NULL)
	{
		if(!$key) return $this->navigation;
		elseif($key and $this->navigation[$key]) return $this->navigation[$key];
		else return false;
	}
	
	// Met à jour l'arbre de navigation à la nouvelle version
	function createStructure($navigation)
	{
		mysql_query("DROP TABLE IF EXISTS `structure`;");
		mysql_query("CREATE TABLE `structure` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `page` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `parent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		  `parent_priority` int(11) NOT NULL,
		  `page_priority` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
		
		$pparent = 1;
		$ppage = 1;
		foreach($navigation as $parent => $pages)
		{
			$ppage = 1;
			$pages = a::beArray($pages);
			foreach($pages as $page)
			{
				$last = db::insert('structure', array('parent' => $parent, 'page' => $page, 'parent_priority' => $pparent, 'page_priority' => $ppage));
				echo $last. ' - ' .$parent.'-'.$page.'<br/>';
				db::update('meta', array('page' => $last), array('page' => $parent.'-'.$page));
				$ppage++;
			}
			
			$pparent++;
		}
	}
	
	/*
	########################################
	######### ARBRES DE NAVIGATION #########
	######################################## 
	*/
	
	// Création des arrays de liens
	function createTree()
	{		
		if(!isset($this->treeNavigation))
			foreach($this->allowedPages as $key)
				if($key != 'admin' or ($key == 'admin' and LOCAL))
					$this->treeNavigation[$key] = rewrite($key, array('subnav' => $this->optionSubnav));			
		
		if(!isset($this->treeSubnav) and $this->optionSubnav)
			foreach($this->navigation[$this->page] as $key)
				$this->treeSubnav[$key] = rewrite($this->page. '-' .$key, array('subnav' => $this->optionSubnav));			
	}
	
	// Altération des liens de la liste
	function alterTree($key, $newLink = NULL, $subTree = false)
	{
		$this->createTree();
		
		if(findString('-', $key))
		{
			$key = a::get(explode('-', $key), 1);
			$subTree = true;
		}
		
		$thisTree = ($subTree) ? 'treeSubnav' : 'treeNavigation';
			
		if(empty($newLink)) $this->{$thisTree} = a::remove($this->{$thisTree}, $key);
		else $this->{$thisTree}[$key] = $newLink;
	}
	
	// Rendu HTML des arbres de navigation
	function render(&$renderPage, &$renderSousPage, &$renderNavigation, &$renderSubnav, $glue = NULL)
	{		
		$this->createTree();
		if(!LOCAL) unset($this->treeNavigation['admin']);

		// Navigation principale
		foreach($this->treeNavigation as $key => $value)
		{
			$texte = l::get('menu-' .$key, ucfirst($key));
			$parametres = array('class' => 'menu-'.$key);
			if($key == $this->page) $parametres['class'] .= ' hover';
			
			// Ecriture du lien
			$lien = (($this->optionMono or $value == 'mono') and $key != 'admin')
				? '<a id="mono-' .$key. '" rel="mono" class="' .$parametres['class']. '">' .$texte. '</a>'
				: $lien = str::link($value, $texte, $parametres);
			
			$keys[] = ($this->optionListed)
				? '<li class="' .$parametres['class']. '">' .$lien. '</li>'
				: $lien;
		}
		
		$renderNavigation = ($this->optionListed)
			? '<ul>' .implode($glue, $keys). '</ul>'
			: implode($glue, $keys);
			unset($keys);
		
		// Sous-navigation
		if(!empty($this->treeSubnav))
		{
			foreach($this->treeSubnav as $key => $value)
			{
				$texte = l::get('menu-' .$this->page. '-' .$key);
				$hover = ($key == $this->pageSub) ? ' class="hover"' : '';
				$keys[] = ($this->optionListedSub)
					? '<li' .$hover.'>' .str::link($value, $texte). '</li>'
					: str::link($value, $texte, $hover);
			}
			$renderSubnav = ($this->optionListedSub)
				? '<ul>' .implode($glue, $keys). '</ul>'
				: implode($glue, $keys);
		}
		else $renderSubnav = NULL;

		// Assignation des valeurs
		$renderPage = $this->page;
		$renderSousPage = $this->pageSub;
	}
	
	/*
	########################################
	############## PAGE EN COURS ###########
	######################################## 
	*/
	
	// Génération du contenu
	function content()
	{
		global $switcher;
		$page = FALSE;
		
		// Chargement de l'admin ou d'une page
		switch($this->page)
		{
			case '404';
				f::inclure('cerberus/include/404.php');
				break;
				
			case 'admin':
				new AdminSetup();
				break;
				
			default:
				if(isset($switcher)) $page = sexist($switcher->path('php').$this->filepath);
				if(!$page) $page = sexist('pages/' .$this->filepath);
				
				if(!$page) prompt('Une erreur est survenue lors du chargement de la page');
				else
				{
					$to_cache = a::get($this->pageDATA, 'cache', TRUE);
					if($to_cache) include content::cache($page, NULL, TRUE);
					else include $page;
				}
				break;
			}
	}
	
	// Récupération de la classe CSS
	function css()
	{
		return $this->page. ' ' .$this->page.'-'.$this->pageSub;
	}
}
?>