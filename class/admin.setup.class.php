<?php
class AdminSetup
{
	// Options
	protected $multilangue; // Site multilangue ou pas
	private $arrayLangues; // Admin multilangue ou pas
	
	// Login
	private $granted; // État de l'accès
	private $loginUser; // Utilisateur
	private $loginPass; // Mot de passe
	
	/*
	########################################
	############## CONSTRUCTION ############
	######################################## 
	*/
	
	function __construct($customNavigation = NULL)
	{	
		global $navigation;
		global $connected;
		
		$this->defineMultilangue();
		
		// Ajout des pages par défaut
		$systemPages = array('meta', 'images', 'backup');
		if(db::is_table('news')) array_unshift($systemPages, 'news');
		$adminNavigation = array_diff($navigation['admin'], array('admin'));
		$thisNavigation = array_merge(a::beArray($customNavigation), $adminNavigation, $systemPages);
	
		// Identification	 
 		if(isset($_GET['logoff'])) unset($_SESSION['admin']);
		$this->adminLogin();
		
		if($this->granted)
		{
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
			echo '<h1>' .$title. '</h1>';
			$this->admin_navigation($thisNavigation);
			
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
			
			if(file_exists('cerberus/include/admin.' .$page. '.php'))
				include_once('cerberus/include/admin.' .$page. '.php');

			elseif(file_exists('pages/admin-' .$page. '.php'))
				include_once('pages/admin-' .$page. '.php');
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
		$admin_form = 
		'<form method="post">
			<fieldset class="login"><legend>Identification</legend>
				<dl>
					<dt>Identifiant</dt>
					<dd><input type="text" name="user" /></dd>
				</dl>
				<dl>
					<dt>Mot de passe</dt>
					<dd><input type="password" name="password" /></dd>
				</dl>
				<dl class="submit">
					<dd><p style="text-align:center"><input type="submit" value="Connexion" /></p></dd> 
				</dl>
			</fieldset>
		</form>';
		
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
		if(db::connection())
		{
			$queryQ = mysqlQuery('SELECT password FROM admin WHERE user="' .md5($user). '"');
			return (isset($queryQ) && md5($password) == $queryQ);
		}
		elseif(db::connection() and isset($this->loginUser)) return (md5($user) == $this->loginUser and md5($password) == $this->loginPass);
		else return FALSE;
	}
	
	// Paramétrage d'identifiants manuels
	function setLogin($user, $password = NULL)
	{
		$this->loginUser = $user;
		$this->loginPass = (!empty($password)) ? $password : $user;
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
	function admin_navigation($navigation)
	{
		echo '<div class="navbar" style="position:relative">';
		
		if(MULTILANGUE and $this->multilangue)
		{
			echo '<p style="position: absolute; right: 5px; top: -7px">';
			// Langue de l'admin
			foreach($this->multilangue as $langue)
			{
				$getAdmin = (isset($_GET['admin'])) ? '&admin=' .$_GET['admin'] : '';
				$urlFlag = (isset($_SESSION['admin']['langue']) and $_SESSION['admin']['langue'] == $langue) ? 'flag_' .$langue : 'flag_' .$langue. '_off';
				echo '<a href="' .rewrite('admin', array('adminLangue' => $langue.$getAdmin)). '"><img src="assets/css/' .$urlFlag. '.png" alt="' .$langue. '" /></a> ';
			}
			echo '</p>';
		}
	
		// Navigation de l'admin
		if(!empty($navigation)) foreach($navigation as $key => $value)
		{
			//if($value == 'news') echo '<br /><br />';
			$textLien = ($this->arrayLangues) ? l::get('admin-' .$value) : ucfirst($value);
			$thisActive = (isset($_GET['admin']) and $value == $_GET['admin']) ? 'class="hover"' : '';
			echo '<a href="' .rewrite('admin-' .$value). '" ' .$thisActive. '>' .$textLien. '</a>';	
		}
		echo 
		'<a href="' .rewrite('admin', 'logoff'). '">Déconnexion</a>
		</div><br />';
	}
}
?>