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
	static private $optionListed = FALSE;
	static private $optionListedSub = FALSE;

	// Paramètres
	static private $homepage;
	static public $page;
	static public $sousPage;
	static private $filepath;
	static private $system = array('404', 'sitemap');
	
	// Rendus
	static private $renderNavigation;
	static private $renderSubnav;
	
	// DONNEES
	static private $data = array();
	static private $rendered = FALSE;
	
	/*
	########################################
	############## MISE EN PLACE ###########
	######################################## 
	*/
	
	// Fonctions moteur
	function __construct()
	{		
		$data = cache::fetch('navigation');
		if($data) self::$data = $data;
		
		// Créations des tables requises
		if(!self::$data)
		{
			// Navigation via la base de données	
			if(SQL and db::is_table('cerberus_structure'))
				$data_raw = db::select('cerberus_structure', '*', NULL, 'parent_priority ASC, page_priority ASC');
			
			else
			{
				// Navigation via fichier config
				$navigation = config::get('navigation');
				if(!$navigation) $navigation = array('home');
				
				foreach($navigation as $page)
				$data_raw[] = array(
					'page' => $page,
					'parent' => NULL,
					'cache' => 0,
					'hidden' => 0,
					'external_link' => NULL);
			}
			self::build($data_raw);
		}		
		
		// Page en cours
		self::$homepage = key(self::$data);
		
		$page = isset(self::$data[get('page')]) ? get('page') : self::$homepage;
		$sousMenu = isset(self::$data[$page]) ? a::get(self::$data[$page], 'submenu', a::get(self::$data[self::$homepage], 'submenu', NULL)) : NULL;
		if($sousMenu) $sousPage = isset($sousMenu[get('pageSub')]) ? get('pageSub') : key($sousMenu);
		else $sousPage = NULL;

		// Détection du chemin vers le fichier à inclure
		if(!in_array($page, self::$system))
		{
			if($page != 'admin') self::$filepath = self::extension($page, $sousPage);
			else if(get('admin')) $sousPage = get('admin');
		}
		
		
		// Page externe
		$path = array_reverse(debug_backtrace());
		$path = f::name($path[0]['file'], true);
		if($page == self::$homepage and 
		   a::get($_GET, 'page') != self::$homepage and 
		   $path != config::get('index'))
		{
			$page = $path;
			$sousPage = NULL;
			$external = true;
		}
		else $external = false;
		define('EXTERNAL', $external);
		
		// Enregistrement des variables
		self::$page = $page;
		self::$sousPage = $sousPage;
		
		self::active();
	}
	
	// Vérification de l'existence d'une page
	static function extension(&$page, &$sousPage)
	{
		$page_combined = $sousPage ? 'pages/'.$page.'-'.$sousPage : 'pages/'.$page;
		if(!file_exists('pages')) dir::make('pages');
		
		// Balayage des noms possibles de la page
		$return = f::path($page_combined.'.html', $page_combined.'.php', $page.'.html', $page.'.php');
		
		// Si non trouvé -> 404
		if(isset($return)) return basename($return);
		else
		{
			if(sizeof(self::$data) != 1 and $page != self::$homepage)
			{
				$page = 404;
				$sousPage = NULL;			
			}
			return FALSE;
		}
	}
	
	// Afficher les menus en ligne ou en liste
	static function listed($menu = FALSE, $submenu = FALSE)
	{
		self::$optionListed = $menu;
		self::$optionListedSub = $submenu;
	}
			
	/*
	########################################
	######### ARBRES DE NAVIGATION #########
	######################################## 
	*/
	
	// Création de l'arbre de navigation
	static function build($data_raw)
	{
		// Ajout des pages système à l'arbre
		foreach(self::$system as $sys)
		{
			$data_raw[] = array(
				'page' => $sys,
				'parent' => NULL,
				'cache' => 1,
				'hidden' => 1,
				'external_link' => NULL);
		}

		// Création de l'arbre de navigation
		foreach($data_raw as $key => $values)
		{
			$simple_tree = empty($values['parent']);
			
			// MENU
			$index = !$simple_tree ? $values['parent'] : $values['page']; // Cas d'une arborescence simple
			if(!isset($data_raw[$index]))
			{
				$lien = NULL;
				$external = 0;
				$hidden = $values['hidden'];
				$subcount = SQL ? db::count('cerberus_structure', array('parent' => $index)) : NULL;
				if($subcount == 1)
				{
					if(!empty($values['external_link']))
					{
						$lien = $values['external_link'];
						$external = 1;
					}
				}
					
				$data_raw[$index] = array(
					'text' => l::get('menu-' .$index, ucfirst($index)),
					'hidden' => $hidden,
					'external' => $external,
					'class' => array('menu-'.$index),
					'link' => $lien);
			}
			
			// SOUS-MENU					
			if(!$simple_tree and $external != '1')
			{
				$index_sub = $values['parent'].'-'.$values['page'];
				$lien = (!empty($values['external_link'])) 
					? $values['external_link']
					: NULL;
		
				$data_raw[$index]['submenu'][$values['page']] = array(
					'hidden' => $values['hidden'],
					'text' => l::get('menu-' .$index_sub, ucfirst($values['page'])),
					'class' => array('menu-'.$index.'-'.$values['page']),
					'link' => $lien);		
							
				// Calculs des liens des sous-pages
				if(isset($data_raw[$index]['submenu']))
					foreach($data_raw[$index]['submenu'] as $subkey => $subvalue)
						if(!a::get($subvalue, 'link'))
							$data_raw[$index]['submenu'][$subkey]['link'] = url::rewrite($values['parent'].'-'.$values['page']);
			}
						
			if(!a::get($values, 'link'))
			{
				// Lien externe
				if($data_raw[$index]['external'] == 1)
					$data_raw[$index]['link'] = $data_raw[$index]['link'];
				
				else
				{
					$submenu = a::get(a::get($data_raw, $index), 'submenu');
					$link = $submenu ? $index.'-'.key($submenu) : $index;
					$data_raw[$index]['link'] = url::rewrite($link);
				}
			}
			
			$data_raw = a::remove($data_raw, $key);
		}
		if(!LOCAL) $data_raw['admin']['hidden'] = 1;
		
		self::$data = $data_raw;
		cache::fetch('navigation', self::$data);
	}

	// Détermine quels liens sont actifs
	static private function active()
	{
		// Active pages
		foreach(self::$data as $key => $value)
		{
			// Page
			if($key == self::$page)
				self::$data[$key]['class'][] = 'active';
				self::$data[$key]['class'] = implode(' ', a::get(self::$data[$key], 'class', array()));
			
			// Sous-page
			if(isset($value['submenu']))
				foreach($value['submenu'] as $subkey => $subvalue)
				{
					if($key == self::$page and $subkey == self::$sousPage)
						self::$data[$key]['submenu'][$subkey]['class'][] = 'active';
						self::$data[$key]['submenu'][$subkey]['class'] = implode(' ', a::get(self::$data[$key]['submenu'][$subkey], 'class', array()));
				}
		}
	}
	
	// Altération des liens de la liste
	static function alterTree($key, $alter_value = NULL, $alter_key = 'link')
	{		
		if(str::find('-', $key))
		{
			$key = explode('-', $key);
			self::$data[$key[0]]['submenu'][$key[1]][$alter_key] = $alter_value;
		}
		else self::$data[$key][$alter_key] = $alter_value;
	}
	
	// Rendu HTML des arbres de navigation
	static function render($glue = NULL)
	{		
		$glue .= PHP_EOL;
		if(empty(self::$renderNavigation))
		{			
			foreach(self::$data as $key => $value)
			{
				if(isset($value['hidden']) and $value['hidden'] != 1)
				{
					// Attributs
					$subpage = key(a::get($value, 'submenu', array()));
					$metapage = meta::page($key. '-' .$subpage);
					$attr['class'] = a::get($value, 'class');
					$attr['title'] = a::get($metapage, 'titre');			
					$classList = $attr['class'] ? ' class="' .$attr['class']. '"' : NULL;
					
					$lien = self::$optionListed
						? '<li' .$classList. '>' .str::link($value['link'], $value['text'], array('title' => $attr['title'])). '</li>'
						: str::link($value['link'], $value['text'], $attr);
					self::$renderNavigation .= $lien.$glue;
				}
				if(isset($value['submenu']))
				{
					self::$renderSubnav[$key] = NULL;
					foreach($value['submenu'] as $subkey => $subvalue)
					{
						if($subvalue['hidden'] != 1)
						{
							// Attributs
							$metapage = meta::page($key.'-'.$subkey);
							$attr['class'] = a::get($subvalue, 'class');
							$attr['title'] = a::get($metapage, 'titre');		
							$classList = $attr['class'] ? ' class="' .$attr['class']. '"' : NULL;
							
							$lien = self::$optionListedSub
								? '<li' .$classList. '>' .str::link($subvalue['link'], $subvalue['text'], array('title' => $attr['title'])). '</li>'
								: str::link($subvalue['link'], $subvalue['text'], $attr);
							self::$renderSubnav[$key] .= $lien.$glue;
						}
					}	
				}
			}
			if(self::$optionListed and isset(self::$renderNavigation)) self::$renderNavigation = '<ul>'.self::$renderNavigation.'</ul>';
			if(self::$optionListedSub and isset(self::$renderSubnav[$key])) self::$renderSubnav[$key] = '<ul>'.self::$renderSubnav[$key].'</ul>';
		}
	}
	
	/*
	########################################
	######### FONCTIONS CONTENU ############
	######################################## 
	*/
	
	// Génération du contenu
	static function content()
	{
		// Chargement de l'admin ou d'une page
		if(self::$page)
		{
			switch(self::$page)
			{
				case '404';
					f::inclure('cerberus/include/404.php');
					break;
					
				case 'sitemap':
					f::inclure('cerberus/include/sitemap.php');
					break;
				
				case 'admin':
					new admin_setup();
					break;
					
				case NULL:
					return false;
					break;
					
				default:
					if(!f::inclure('pages/' .self::$filepath))
					{
						$error = str_replace('{filepath}', self::$filepath, l::get('error.filepath'));
						str::display($error, 'error');
						errorHandle('Warning', 'Le fichier ' .self::$filepath. ' est introuvable', __FILE__, __LINE__);
					}
					break;
			}
		}
	}
	
	// Fil d'arianne
	static function ariane($home = NULL)
	{
		$home = config::get('sitename', $home);
		$ariane = ($home) ? str::link('index.php', $home). ' > ' : NULL;
		return $ariane . str::slink(self::$page, l::get('menu-' .self::$page)). ' > ' .str::slink(self::$sousPage, l::get('menu-' .self::$page. '-' .self::$sousPage));
	}
	
	// Pied de page
	static function footer($links = array())
	{
		$footer = '&copy;Copyright ' .date('Y');
		if(config::get('sitename')) $footer .= ' - ' .config::get('sitename');
		if(SQL and db::is_table('cerberus_structure')) $footer .= ' - ' .str::slink('sitemap', l::get('menu-sitemap'));
		$footer .= ' - Conception : ' .str::link('http://www.stappler.fr/', 'Le Principe de Stappler');
		if(isset(self::$data['contact']['submenu']['legales'])) $footer .= ' - ' .str::slink('contact-legales', l::get('menu-contact-legales'));
		if(isset(self::$data['contact']['submenu']['contact'])) $footer .= ' - ' .str::slink('contact', l::get('menu-contact'));
		if(!empty($links))
		{
			foreach($links as $link => $text)
				$footer .= ' - ' .str::link($link, $text);
		}
		return $footer;
	}
	
	/*
	########################################
	############## EXPORTS #################
	######################################## 
	*/
		
	// Vérifie la présence d'une clé dans l'arbre
	static function get($key = NULL)
	{
		if(!$key) return self::$data;
		elseif($key and isset(self::$data[$key])) return self::$data[$key];
		else return false;
	}
	
	// Récupére le menu rendu
	static function getMenu($render = TRUE)
	{
		if($render) self::render();
		return ($render) ? self::$renderNavigation : self::get();
	}
	
	static function getSubmenu($render = TRUE)
	{
		$submenu = a::get(self::$data, self::$page.',submenu');
		if($render)
		{
			self::render();
			return ($submenu and self::$page != 'admin' and count($submenu) > 1) ? self::$renderSubnav[self::$page] : NULL;
		}
		
		else
			return $submenu;
	}

	// Page en cours
	static function current()
	{
		return self::$sousPage ? self::$page. '-' .self::$sousPage : self::$page;
	}
		
	// Récupération de la classe CSS
	static function css()
	{
		return self::$page != self::current()
			? self::$page. ' ' .self::current()
			: self::$page;
	}
}
?>