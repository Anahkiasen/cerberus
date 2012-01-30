<?php
/*
	Classe Navigation
	# Détermine la page en cours et construit les menus à partir d'un arbre de navigation
	
	$navigation
		Arbre de navigation du site en cours au format correspondant
		selon si le site dispose ou non d'une sous-navigation, et/ou
		est multilangue ou non

	On fournit à la classe un arbre de type {PARENT:{CHILD, CHILD},PARENT:{CHILD,CHILD}}
*/
class navigation
{
	// Options de fonctionnement	
	private $optionListed = FALSE;
	private $optionListedSub = FALSE;

	// Paramètres
	public $page;
	public $sousPage;
	private $system = array('404', 'sitemap');
	
	// Rendus
	private $renderNavigation;
	private $renderSubnav;
	
	// DONNEES
	private $data = array();
	private $rendered = FALSE;
	
	/*
	########################################
	############## MISE EN PLACE ###########
	######################################## 
	*/
	
	// Fonctions moteur
	function __construct()
	{
		$navigation = config::get('navigation');
		
		// Créations des tables requises		
		if(!$navigation)
		{
			if(SQL and db::is_table('cerberus_structure'))
				$this->data = db::select('cerberus_structure', '*', NULL, 'parent_priority ASC, page_priority ASC');
		}
		else
		{
			foreach($navigation as $page)
			$this->data[] = array(
				'page' => $page,
				'parent' => NULL,
				'cache' => 0,
				'hidden' => 0,
				'external_link' => NULL);
		}
		if($this->data)
		{
			// Pages système
			foreach($this->system as $sys)
			{
				$this->data[] = array(
					'page' => $sys,
					'parent' => NULL,
					'cache' => 1,
					'hidden' => 1,
					'external_link' => NULL);
			}
	
			// CERBERUS_STRUCTURE
			foreach($this->data as $key => $values)
			{
				// MENU
				$index = !empty($values['parent']) ? $values['parent'] : $values['page']; // Cas d'une arborescence simple
				if(!isset($this->data[$index]))
				{
					$lien = NULL;
					$subcount = db::count('cerberus_structure', array('parent' => $index));
					if($subcount == 1)
					{
						$hidden = $values['hidden'];
						if(!empty($values['external_link'])) $lien = $values['external_link'];
					}
					else $hidden = $subcount > 1 ? 0 : 1;
						
					$this->data[$index] = array(
						'text' => l::get('menu-' .$index, ucfirst($index)),
						'hidden' => $hidden,
						'link' => $lien);
				}
				
				// SOUS-MENU					
				if(!empty($values['parent']))
				{
					$index = $values['parent'].'-'.$values['page'];
					$lien = (!empty($values['external_link'])) 
						? $values['external_link']
						: NULL;
						
					$this->data[$values['parent']]['submenu'][$values['page']] = array(
						'hidden' => $values['hidden'],
						'text' => l::get('menu-' .$index, ucfirst($values['page'])),
						'link' => $lien);						
				}
					
				unset($this->data[$key]);
			}
			if(!LOCAL) $this->data['admin']['hidden'] = 1;
	
			// Page en cours
			$page = isset($this->data[get('page')]) ? get('page') : 'home';
			$sousMenu = isset($this->data[$page]) ? a::get($this->data[$page], 'submenu', a::get($this->data['home'], 'submenu', NULL)) : NULL;
			if($sousMenu) $sousPage = isset($sousMenu[get('pageSub')]) ? get('pageSub') : key($sousMenu);
			else $sousPage = NULL;
	
			// Détection du chemin vers le fichier à inclure
			if(!in_array($page, $this->system))
			{
				if($page != 'admin') $this->filepath = $this->extension($page, $sousPage);
				else if(get('admin')) $sousPage = get('admin');
			}
			
			// Enregistrement des variables
			$this->page = $page;
			$this->sousPage = $sousPage;
		}
	}
	
	// Vérification de l'existence d'une page
	function extension(&$page, &$sousPage)
	{
		$page_combined = $sousPage ? $page.'-'.$sousPage : $page;
		if(!file_exists('pages')) mkdir('pages');
		if(file_exists('pages/' .$page_combined. '.html')) return $page_combined.'.html';
		elseif(file_exists('pages/' .$page_combined. '.php')) return $page_combined.'.php';
		else 
		{
			$page = 404;
			$sousPage = NULL;
			return 'FALSE';
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
		if(!$this->rendered)
		{
			foreach($this->data as $key => $value)
			{
				// Page
				if($key == $this->page)
					$this->data[$key]['class'][] = 'active';
				
				$this->data[$key]['class'] = implode(' ', a::get($this->data[$key], 'class', array()));
				
				if(!$value['link'])
					$this->data[$key]['link'] = url::rewrite($key);
				
				// Sous-page
				if(isset($value['submenu']))
					foreach($value['submenu'] as $subkey => $subvalue)
					{
						if($key == $this->page and $subkey == $this->sousPage)
							$this->data[$key]['submenu'][$subkey]['class'][] = 'active';
						
						$this->data[$key]['submenu'][$subkey]['class'] = implode(' ', a::get($this->data[$key]['submenu'][$subkey], 'class', array()));
						
						if(!$subvalue['link'])
							$this->data[$key]['submenu'][$subkey]['link'] = url::rewrite($key.'-'.$subkey);
					}
			}
			$this->rendered = TRUE;
		}
	}
	
	// Altération des liens de la liste
	function alterTree($key, $newLink = NULL)
	{
		$this->createTree();
		
		if(str::find('-', $key))
		{
			$key = explode('-', $key);
			$this->data[$key[0]]['submenu'][$key[1]]['link'] = $newLink;
		}
		else $this->data[$key[0]]['link'] = $newLink;
	}
	
	// Rendu HTML des arbres de navigation
	function render($glue = NULL)
	{		
		$glue .= PHP_EOL;
		$this->createTree();

		foreach($this->data as $key => $value)
		{
			if($value['hidden'] != 1)
			{
				$class = a::get($value, 'class');
				$classList = $class ? ' class="' .$class. '"' : NULL;
				$lien = $this->optionListed
					? '<li' .$classList. '>' .str::link($value['link'], $value['text']). '</li>'
					: str::link($value['link'], $value['text'], array('class' => $class));
				$this->renderNavigation .= $lien.$glue;
			}
			if(isset($value['submenu']))
			{
				$this->renderSubnav[$key] = NULL;
				foreach($value['submenu'] as $subkey => $subvalue)
				{
					if($subvalue['hidden'] != 1)
					{
						$class = a::get($subvalue, 'class');
						$classList = $class ? ' class="' .$class. '"' : NULL;
						$lien = $this->optionListedSub
							? '<li' .$classList. '>' .str::link($subvalue['link'], $subvalue['text']). '</li>'
							: str::link($subvalue['link'], $subvalue['text'], array('class' => $class));
						$this->renderSubnav[$key] .= $lien.$glue;
					}
				}	
			}
		}
		if($this->optionListed and isset($this->renderNavigation)) $this->renderNavigation = '<ul>'.$this->renderNavigation.'</ul>';
		if($this->optionListedSub and isset($this->renderSubnav[$key])) $this->renderSubnav[$key] = '<ul>'.$this->renderSubnav[$key].'</ul>';
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
				if(!f::inclure('pages/' .$this->filepath))
				{
					$error = str_replace('{filepath}', $this->filepath, l::get('error.filepath'));
					str::display($error, 'error');
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
			' .str::slink('sitemap', l::get('menu-sitemap')). ' - 
			Conception : ' .str::link('http://www.stappler.fr/', 'Le Principe de Stappler');
		if(isset($this->data['contact']['submenu']['legales'])) $footer .= ' - ' .str::slink('contact-legales', l::get('menu-contact-legales'));
		if(isset($this->data['contact']['submenu']['contact'])) $footer .= ' - ' .str::slink('contact', l::get('menu-contact'));
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
		if(!$key) return $this->data;
		elseif($key and isset($this->data[$key])) return $this->data[$key];
		else return false;
	}
	
	// Récupére le menu rendu
	function getMenu($render = TRUE)
	{
		return ($render) ? $this->renderNavigation : $this->get();
	}
	
	function getSubmenu($render = TRUE)
	{
		$submenu = a::get($this->data[$this->page], 'submenu');
		if($render)
			return ($submenu and $this->page != 'admin' and count($submenu) > 1) ? $this->renderSubnav[$this->page] : NULL;
		
		else
			return $submenu;
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