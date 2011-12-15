<?php
class AdminSetup
{
	// Options
	protected $multilangue; // Site multilangue ou pas
	private $arrayLangues; // Admin multilangue ou pas
	
	// Login
	private $granted; // État de l'accès
	private $login_user; // Utilisateur
	private $login_password; // Mot de passe
	
	// Navigation
	private static $droits;
	private $navigation = array('website' => array(), 'systeme' => array());
	
	/*
	########################################
	############## CONSTRUCTION ############
	######################################## 
	*/
	
	function __construct($customNavigation = NULL)
	{	
		global $connected, $desired;

		$this->defineMultilangue();
			
		// Identification	 
 		if(isset($_GET['logoff'])) s::remove('admin');
		$this->login_user = md5(config::get('admin.login', 'root'));
		$this->login_password = md5(config::get('admin.password', ''));
		$this->adminLogin();
		
		if($this->granted)
		{
			// Création de la navigation de l'admin
			$this->navigation['systeme'] = array('images', 'Cache' => 'crawler', 'Configuration' => 'config');
			if(SQL)
			{
				if(config::get('multi_admin')) $this->navigation['systeme']['Utilisateurs'] = 'admin';
				$this->navigation['systeme']['Sauvegardes'] = 'backup';
				if(db::is_table('cerberus_structure')) array_unshift($this->navigation['systeme'], 'structure');
				if(MULTILANGUE) array_unshift($this->navigation['systeme'], 'langue');
				if(db::is_table('cerberus_news')) $this->navigation['website']['Actualités'] = 'news';
			}
			$this->navigation['website'] = array_merge($this->navigation['website'], array_diff($desired->get('admin'), array('admin')));
			$this->navigation['systeme'] = array_merge($this->navigation['systeme'], a::force_array($customNavigation));
		
			// Droits de l'utilisateur
			self::$droits = (SQL and db::is_table('cerberus_admin')) 
				? str::parse(db::field('cerberus_admin', 'droits', array('user' => md5($_SESSION['admin']['user'])))) 
				: NULL;
				if(empty(self::$droits)) 
					foreach($this->navigation as $section => $pages) 
						foreach($pages as $page) self::$droits[$page] = TRUE;

			// Vérification de la page
			$title = 'Administration';
			if(!empty($this->navigation))
			{
				$admin = get('admin');
				if(	isset($admin) and
					in_array($admin, self::$droits))
						
					$title = ($this->arrayLangues) ? l::get('menu-admin-' .$admin) : ucfirst($admin);
			}
			
			// Affichage de la page		
			echo '<div id="admin">';
			$this->admin_navigation();
			$this->content();
			echo '</div>';
		}
	}
	
	// Charger une page d'admin
	function content()
	{
		if(isset($_GET['admin']))
		{
			$page = get('admin');
			$include = f::inclure('cerberus/include/admin.' .$page. '.php');
			if(!$include) f::inclure('pages/admin-' .$page. '.php');
		}
	}
	
	// Admin en plusieures langues
	function defineMultilangue($arrayLangues = NULL)
	{		
		$this->arrayLangues = $arrayLangues;
		if(MULTILANGUE)
		{
			$this->multilangue = config::get('langues', array(config::get('langue_default', 'fr')));
			if(count($this->multilangue) == 1) $this->multilangue = FALSE;
		}
		else $this->multilangue = FALSE;
		return $this->multilangue;
	}
	
	/*
	########################################
	############# IDENTIFICATION ###########
	########################################
	*/
	
	// Formulaire d'identification et vérification
	function adminLogin()
	{
		$admin_form = new form(false);
		$admin_form->openFieldset('Identification');
			$admin_form->addText('user', 'Identifiant');
			$admin_form->addPass('password', 'Mot de passe');
			$admin_form->addSubmit('Connexion');
		$admin_form->closeFieldset();
				
		// Vérification du formulaire		
		if(isset($_POST['user'], $_POST['password']))
		{
			if($this->checkLogin($_POST['user'], $_POST['password']))
			{
				$_SESSION['admin']['user'] = $_POST['user'];
				$_SESSION['admin']['password'] = $_POST['password'];
				$this->granted = TRUE;
			}
			else echo display('Les identifiants entrés sont incorrects.').$admin_form;
		}
		elseif(isset($_SESSION['admin']['user'], $_SESSION['admin']['password']) and $this->checkLogin($_SESSION['admin']['user'], $_SESSION['admin']['password'])) $this->granted = TRUE;
		else echo display('Veuillez entrer votre identifiant et mot de passe.').$admin_form;
	}
	
	// Vérification des identifiants
	function checkLogin($user, $password)
	{
		if(db::connection() and db::is_table('cerberus_admin'))
		{
			$queryQ = db::field('cerberus_admin', 'password', array('user' => md5($user)));
			return (isset($queryQ) && md5($password) == $queryQ);
		}
		else return (md5($user) == $this->login_user and md5($password) == $this->login_password);
	}
	
	// Recupération de l'identification
	function accessGranted()
	{
		return $this->granted;
	}
	
	/*
	########################################
	############## NAVIGATION ##############
	######################################## 
	*/
	function admin_navigation()
	{
		echo '<div class="navbar" style="position:relative">';
		
		// Langue de l'admin
		if(MULTILANGUE and $this->multilangue)
		{
			echo '<p style="position: absolute; right: 5px; top: -7px">';
			foreach($this->multilangue as $langue)
			{
				$flag_state = (l::admin_current() == $langue) ? NULL : '_off';
				echo str::slink(NULL, str::img('assets/css/flag_' .$langue.$flag_state. '.png', $langue), array('adminLangue' => $langue));
			}
			echo '</p>';
		}
	
		// Navigation de l'admin
		asort($this->navigation);
		if(!empty($this->navigation))
		foreach($this->navigation as $sections => $pages)
		{
			if($sections == 'systeme') echo '</div><div class="navbar bottom">';
			foreach($pages as $titre => $page)
			{
				if(!empty($page) and self::$droits[$page])
				{
					// Texte
					$texte_lien = (!is_numeric($titre)) ? $titre : l::getalt('menu-admin-'.$page, l::admin_current(), $page, TRUE); 
					$thisActive = (isset($_GET['admin']) and $page == $_GET['admin']) ? array('class' => 'hover') : NULL;
					echo str::slink('admin-' .$page, $texte_lien, NULL, $thisActive);
				}
			}
		}
		echo str::slink('admin', 'Déconnexion', 'logoff').'</div><br />';
	}
	
	function getNavigation()
	{
		return $this->navigation;
	}
	
	function get($variable)
	{
		return self::${$variable};
	}
}
?>