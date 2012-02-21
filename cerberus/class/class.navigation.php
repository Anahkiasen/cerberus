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
		$navigation = config::get('navigation');
		
		// Créations des tables requises		
		if(!$navigation)
		{
			if(SQL and db::is_table('cerberus_structure'))
				self::$data = db::select('cerberus_structure', '*', NULL, 'parent_priority ASC, page_priority ASC');
		}
		else
		{
			foreach($navigation as $page)
			self::$data[] = array(
				'page' => $page,
				'parent' => NULL,
				'cache' => 0,
				'hidden' => 0,
				'external_link' => NULL);
		}
		if(self::$data)
		{
			// Pages système
			foreach(self::$system as $sys)
			{
				self::$data[] = array(
					'page' => $sys,
					'parent' => NULL,
					'cache' => 1,
					'hidden' => 1,
					'external_link' => NULL);
			}
	
			// CERBERUS_STRUCTURE
			foreach(self::$data as $key => $values)
			{
				// MENU
				$index = !empty($values['parent']) ? $values['parent'] : $values['page']; // Cas d'une arborescence simple
				if(!isset(self::$data[$index]))
				{
					$lien = NULL;
					$external = 0;
					$subcount = db::count('cerberus_structure', array('parent' => $index));
					if($subcount == 1)
					{
						$hidden = $values['hidden'];
						if(!empty($values['external_link']))
						{
							$lien = $values['external_link'];
							$external = 1;
						}
					}
					else $hidden = $subcount > 1 ? 0 : 1;
						
					self::$data[$index] = array(
						'text' => l::get('menu-' .$index, ucfirst($index)),
						'hidden' => $hidden,
						'external' => $external,
						'link' => $lien);
				}
				
				// SOUS-MENU					
				if(!empty($values['parent']))
				{
					$index = $values['parent'].'-'.$values['page'];
					$lien = (!empty($values['external_link'])) 
						? $values['external_link']
						: NULL;
						
					self::$data[$values['parent']]['submenu'][$values['page']] = array(
						'hidden' => $values['hidden'],
						'text' => l::get('menu-' .$index, ucfirst($values['page'])),
						'link' => $lien);						
				}
					
				self::$data = a::remove(self::$data, $key);
			}
			if(!LOCAL) self::$data['admin']['hidden'] = 1;
			
			// Page en cours
			$default_page = key(self::$data);
			
			$page = isset(self::$data[get('page')]) ? get('page') : $default_page;
			$sousMenu = isset(self::$data[$page]) ? a::get(self::$data[$page], 'submenu', a::get(self::$data[$default_page], 'submenu', NULL)) : NULL;
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
			if($page == $default_page and 
			   a::get($_GET, 'page') != $default_page and 
			   $path != config::get('index', 'index'))
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
		}
	}
	
	// Vérification de l'existence d'une page
	static function extension(&$page, &$sousPage)
	{
		$page_combined = $sousPage ? $page.'-'.$sousPage : $page;
		if(!file_exists('pages')) dir::make('pages');
		
		// Balayage des noms possibles de la page
		$possible = array($page_combined.'.html', $page_combined.'.php', $page.'.html', $page.'.php');
		foreach($possible as $p)
			if(!isset($return)) $return = f::path('pages/'.$p);
			else break;
					
		// Si non trouvé -> 404
		if(isset($return)) return basename($return);
		else
		{
			$page = 404;
			$sousPage = NULL;
			return 'FALSE';	
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
	
	// Création des arrays de liens
	static function createTree()
	{
		if(!self::$rendered)
		{
			foreach(self::$data as $key => $value)
			{
				// Page
				if($key == self::$page)
					self::$data[$key]['class'][] = 'active';
				
				self::$data[$key]['class'] = implode(' ', a::get(self::$data[$key], 'class', array()));
				
				if(!a::get($value, 'link'))
					self::$data[$key]['link'] = url::rewrite($key);
				
				// Sous-page
				if(isset($value['submenu']))
					foreach($value['submenu'] as $subkey => $subvalue)
					{
						if($key == self::$page and $subkey == self::$sousPage)
							self::$data[$key]['submenu'][$subkey]['class'][] = 'active';
						
						self::$data[$key]['submenu'][$subkey]['class'] = implode(' ', a::get(self::$data[$key]['submenu'][$subkey], 'class', array()));
						
						if(!$subvalue['link'])
							self::$data[$key]['submenu'][$subkey]['link'] = url::rewrite($key.'-'.$subkey);
					}
			}
			self::$rendered = TRUE;
		}
	}
	
	// Altération des liens de la liste
	static function alterTree($key, $newLink = NULL)
	{
		self::createTree();
		
		if(str::find('-', $key))
		{
			$key = explode('-', $key);
			self::$data[$key[0]]['submenu'][$key[1]]['link'] = $newLink;
		}
		else self::$data[$key]['link'] = $newLink;
	}
	
	// Rendu HTML des arbres de navigation
	static function render($glue = NULL)
	{		
		$glue .= PHP_EOL;
		if(empty(self::$renderNavigation))
		{
			self::createTree();
			
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
						? '<li' .$classList. '>' .str::link($value['link'], $value['text']). '</li>'
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
								? '<li' .$classList. '>' .str::link($subvalue['link'], $subvalue['text']). '</li>'
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
		echo '<div class="' .self::current(). '-content ' .self::current_page(). '-content">';
		switch(self::$page)
		{
			case '404';
				f::inclure('cerberus/include/404.php');
				break;
				
			case 'sitemap':
				f::inclure('cerberus/include/sitemap.php');
				break;
			
			case 'admin':
				global $cerberus;
				$cerberus->injectModule('class.admin.setup', 'class.admin', 'class.form', 'class.forms');
				new AdminSetup();
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
		echo '</div>';
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
		$footer =
			'&copy;Copyright ' .date('Y'). ' - 
			' .config::get('sitename'). ' - 
			' .str::slink('sitemap', l::get('menu-sitemap')). ' - 
			Conception : ' .str::link('http://www.stappler.fr/', 'Le Principe de Stappler');
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
		$submenu = a::get(a::get(self::$data, self::$page), 'submenu');
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
		static function current_page()
		{
			return self::$page;
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