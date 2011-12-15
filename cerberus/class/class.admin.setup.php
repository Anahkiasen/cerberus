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
			// Ajout des pages par défaut
			$systemPages = array('images', 'Cache' => 'crawler', 'Configuration' => 'config');
			if(SQL)
			{
				$systemPages['Sauvegardes'] = 'backup';
				if(db::is_table('cerberus_structure')) array_unshift($systemPages, 'structure');
				if(MULTILANGUE) array_unshift($systemPages, 'langue');
				if(db::is_table('cerberus_news')) array_unshift($systemPages, 'news');
			}
						
			$adminNavigation = array_diff($desired->get('admin'), array('admin'));
			$thisNavigation = array_merge(a::force_array($customNavigation), $adminNavigation, $systemPages);
		
			// Droits de l'utilisateur
			$droits = (SQL and db::is_table('cerberus_admin')) ? str::parse(db::field('cerberus_admin', 'droits', array('user' => md5($_SESSION['admin']['user'])))) : NULL;
			if(!empty($droits))
			{
				foreach($droits as $page)
					if(in_array($page, $thisNavigation)) $sanitizedNavigation[] = $page;
				$thisNavigation = $sanitizedNavigation;
			}
			
			// Vérification de la page
			$title = 'Administration';
			if(!empty($thisNavigation))
			{
				$admin = get('admin');
				if(	isset($admin) and
					in_array($admin, $thisNavigation) and
					(file_exists('pages/admin-' .$admin. '.php')
						or in_array($admin, $systemPages)))
						
					$title = ($this->arrayLangues) ? l::get('admin-' .$admin) : ucfirst($admin);
			}
			
			// Navigation
			$cesure = ($systemPages[0] == 'news') ? $systemPages[1] : $systemPages[0];
			$this->admin_navigation($thisNavigation, $cesure);
			
			echo '<div id="admin">';
			if($title != 'Administration') $this->content();
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
	function admin_navigation($navigation, $cesure)
	{
		echo '<div class="navbar" style="position:relative">';
		
		if(MULTILANGUE and $this->multilangue)
		{
			echo '<p style="position: absolute; right: 5px; top: -7px">';
			// Langue de l'admin
			foreach($this->multilangue as $langue)
			{
				$flag_state = (l::admin_current() == $langue) ? NULL : '_off';
				echo str::slink(NULL, str::img('assets/css/flag_' .$langue.$flag_state. '.png', $langue), array('adminLangue' => $langue));
			}
			echo '</p>';
		}
	
		// Navigation de l'admin
		if(!empty($navigation))
		foreach($navigation as $key => $value)
		{
			if(!empty($value))
			{
				if($value == $cesure) echo '</div><div class="navbar" style="background-image:url(assets/css/overlay/noir-75.png)">';
				
				// Texte
				$texte_lien = (!is_numeric($key)) ? $key : l::getalt('menu-admin-'.$value, l::admin_current(), $value, TRUE); 
				$thisActive = (isset($_GET['admin']) and $value == $_GET['admin']) ? array('class' => 'hover') : NULL;
				echo str::slink('admin-' .$value, $texte_lien, NULL, $thisActive);
			}	
		}
		echo str::slink('admin', 'Déconnexion', 'logoff').'</div><br />';
	}
}
?>