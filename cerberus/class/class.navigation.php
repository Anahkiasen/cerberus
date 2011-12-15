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
	public $page = 'home';
	public $sousPage;
	private $allowedPages;
	private $system = array('404', 'sitemap');

	// Caches
	private $navigation;
	private $treeNavigation;
	private $treeSubnav;

	// Rendus
	private $renderNavigation;
	private $renderSubnav;
	private $data;
	
	/*
	########################################
	############## MISE EN PLACE ###########
	######################################## 
	*/
	
	// Fonctions moteur
	function __construct($navigation = NULL)
	{
		if(SQL)
		{
			if(!$navigation and db::is_table('cerberus_structure'))
			{
				$this->data = db::select('cerberus_structure', '*', NULL, 'parent_priority ASC, page_priority ASC');
				foreach($this->data as $key => $values)
				{
					$navigation[$values['parent']][] = $values['page'];
					$this->data[$values['parent'].'-'.$values['page']] = array('hidden' =>$values['hidden'], 'external' => $values['external_link']);
					unset($this->data[$key]);
				}
			}
			elseif(!db::is_table('cerberus_structure')) update::table('cerberus_structure');
		}
			
		// Navigation par défaut
		if(!isset($navigation) or empty($navigation))
			 $navigation = array(
			 	'home' => array('home'),
				'admin' => array('admin'));
		
		// Options et modes
		$allowed_pages = array_keys($navigation);
		foreach($this->system as $include) $allowed_pages[] = $include;
		
		$sousPage = NULL;
		$page = (isset($_GET['404'])) ? '404' : $allowed_pages[0];
		$this->optionSubnav = (isset($navigation[$page]) and is_array($navigation[$page]));
		$this->options = str::boolprint(MULTILANGUE).str::boolprint($this->optionSubnav);

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
			$sousPage = (get('pageSub') and in_array(get('pageSub'), $navigation[$page]))
				? get('pageSub')
				: $navigation[$page][0];
		}
		else $substring = NULL;

		// Include de la page
		if(!in_array($page, $this->system))
		{
			if($page != 'admin')
			{
				$extension = $this->extension($page.$substring.$sousPage);
				if(!$extension)
				{
					$page = $allowed_pages[0];
					if($this->optionSubnav)
					{
						$substring = '-';
						$sousPage = $navigation[$page][0];
					}
					$extension = $this->extension($page.$substring.$sousPage);
				}
				$this->filepath = $page.$substring.$sousPage.$extension;
			}
			else if(get('admin')) $sousPage = get('admin');
		}
		
		// Simplification des alterations
		foreach($navigation as $parent => $pages)
		{
			if(count($pages) == 1)
			{
				$data = $this->data[$parent.'-'.$pages[0]];
				if(!empty($data['external'])) $this->data[$parent]['external'] = $data['external'];
				if($data['hidden'] == 1) $this->data[$parent]['hidden'] = 1;
			}
		}
		if(!LOCAL) $this->data['admin']['hidden'] = 1;
		foreach($this->system as $sys) $this->data[$sys]['hidden'] = 1;
		
		// Enregistrement des variables
		$this->navigation = $navigation;
		$this->page = $page;
		$this->sousPage = $sousPage;
		$this->allowedPages = $allowed_pages;
	}
	
	// Vérification de l'existence d'une page
	function extension($page, $cerberus = false)
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
			
	/*
	########################################
	######### ARBRES DE NAVIGATION #########
	######################################## 
	*/
	
	// Création des arrays de liens
	function createTree()
	{		
		global $cerberus;
		$cerberus->injectModule('rewrite');
		
		if(!isset($this->treeNavigation))
		{
			foreach($this->allowedPages as $key)
				if(!isset($this->data[$key]['hidden']))
					$this->treeNavigation[$key] = (isset($this->data[$key]['external']))
						? $this->data[$key]['external']
						: rewrite($key, array('subnav' => $this->optionSubnav));
		}		

		if(!isset($this->treeSubnav) and $this->optionSubnav and isset($this->navigation[$this->page]))
		{
			foreach($this->navigation[$this->page] as $key)
				if($this->data[$this->page.'-'.$key]['hidden'] != 1)
					$this->treeSubnav[$key] = (!empty($this->data[$this->page.'-'.$key]['external_link']))
						? $this->data[$this->page.'-'.$key]['external_link']
						: rewrite($this->page. '-' .$key, array('subnav' => $this->optionSubnav));
		}
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
	function render($glue = NULL, &$renderPage = NULL, &$renderSousPage = NULL, &$renderNavigation = NULL, &$renderSubnav = NULL)
	{		
		$glue .= PHP_EOL;
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
				$texte = l::get('menu-' .$this->page. '-' .$key, ucfirst($key));
				$parametres = array('class' => 'menu-' .$this->page. '-'.$key);
				if($key == $this->sousPage) $parametres['class'] .= ' hover';
				
				$keys[] = ($this->optionListedSub)
					? '<li class="' .$parametres['class']. '">' .str::link($value, $texte). '</li>'
					: str::link($value, $texte, $parametres);
			}
			$renderSubnav = ($this->optionListedSub)
				? '<ul>' .implode($glue, $keys). '</ul>'
				: implode($glue, $keys);
		}
		else $renderSubnav = NULL;

		// Assignation des valeurs
		$this->renderNavigation = $renderNavigation;
		$this->renderSubnav = $renderSubnav;
		$renderPage = $this->page;
		$renderSousPage = $this->sousPage;
	}
	
	/*
	########################################
	######### FONCTIONS CONTENU ############
	######################################## 
	*/
	
	// Génération du contenu
	function content()
	{
		// Chargement de l'admin ou d'une page
		echo '<div class="' .$this->current(). '-content ' .$this->current(false). '-content">';
		switch($this->page)
		{
			case '404';
				f::inclure('cerberus/include/404.php');
				break;
				
			case 'sitemap':
				f::inclure('cerberus/include/sitemap.php');
				break;
			
			case 'admin':
				global $cerberus;
				$cerberus->injectModule('class.admin.setup', 'class.admin', 'class.form');
				new AdminSetup();
				break;
				
			default:
				$inclure = f::inclure('pages/' .$this->filepath);
				if(!$inclure)
				{
					prompt('Le fichier ' .$this->filepath. ' est introuvable');
					errorHandle('Warning', 'Le fichier ' .$this->filepath. ' est introuvable', __FILE__, __LINE__);
				}
				break;
		}
		echo '</div>';
	}
	
	// Fil d'arianne
	function ariane($home = NULL)
	{
		$home = config::get('sitename', $home);
		$ariane = ($home) ? str::link('index.php', $home). ' > ' : NULL;
		return $ariane . str::slink($this->page, l::get('menu-' .$this->page)). ' > ' .str::slink($this->sousPage, l::get('menu-' .$this->page. '-' .$this->sousPage));
	}
	
	// Pied de page
	function footer()
	{
		$footer =
			'&copy;Copyright ' .date('Y'). ' - 
			' .config::get('sitename'). ' - 
			' .str::slink('sitemap', l::get('sitemap', 'Plan du site')). ' - 
			Conception : ' .str::link('http://www.stappler.fr/', 'Le Principe de Stappler');
		if(in_array('legales', $this->navigation['contact'])) $footer .= ' - ' .str::slink('contact-legales', l::get('menu-contact-legales', 'Contact'));
		if(in_array('contact', $this->navigation['contact'])) $footer .= ' - ' .str::slink('contact', l::get('menu-contact', 'Contact'));
		return $footer;
	}
	
	/*
	########################################
	############## EXPORTS #################
	######################################## 
	*/
	
	// Vérifie la présence d'une clé dans l'arbre
	function get($key = NULL)
	{
		if(!$key) return $this->navigation;
		elseif($key and isset($this->navigation[$key])) return $this->navigation[$key];
		else return false;
	}
	
	// Récupère le menu rendu
	function getmenu()
	{
		return $this->renderNavigation;
	}
	
	function getsub()
	{
		$subnav = $this->get($this->page);
		if($subnav and count($subnav) != 1 and $this->page != 'admin') return $this->renderSubnav;
		else return false;
	}

	// Page en cours
	function current($getsub = true)
	{
		return ($this->sousPage and $getsub) ? $this->page. '-' .$this->sousPage : $this->page;
	}
		
	// Récupération de la classe CSS
	function css()
	{
		return $this->page. ' ' .$this->current();
	}
}
?>