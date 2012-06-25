<?php
class Admin_Setup
{
	// Options
	protected $multilangue; // Site multilangue ou pas
	private $arrayLangues; // Admin multilangue ou pas

	// Login
	private $granted; // État de l'accès
	private $loginUser; // Utilisateur
	private $loginPassword; // Mot de passe

	// Navigation
	private static $droits;
	private static $navigation = array('website' => array(), 'systeme' => array());

	/*
	########################################
	############## CONSTRUCTION ############
	########################################
	*/

	public function __construct($customNavigation = null)
	{
		global $connected;

		$this->defineMultilangue();

		// Identification
 		if(isset($_GET['logoff'])) session::remove('admin');
		$this->loginUser = md5(config::get('admin.login'));
		$this->loginPassword = md5(config::get('admin.password'));
		$this->adminLogin();

		if($this->granted)
		{
			// Création de la navigation de l'admin
			self::$navigation['systeme'] = array('images', 'Cache' => 'crawler', 'Configuration' => 'config');
			if(SQL)
			{
				if(config::get('multi_admin')) self::$navigation['systeme']['Utilisateurs'] = 'admin';
				self::$navigation['systeme']['Sauvegardes'] = 'backup';
				if(db::is_table('cerberus_structure')) array_unshift(self::$navigation['systeme'], 'structure');
				if(MULTILANGUE) array_unshift(self::$navigation['systeme'], 'langue');
				if(db::is_table('cerberus_news')) self::$navigation['website']['Actualités'] = 'news';
			}

			foreach(navigation::getSubmenu(false) as $index => $name) if($index != 'admin') self::$navigation['website'][$name['text']] = $index;
			self::$navigation['systeme'] = array_merge(self::$navigation['systeme'], a::force_array($customNavigation));

			// Droits de l'utilisateur
			self::$droits = (SQL and db::is_table('cerberus_admin'))
				? str::parse(db::field('cerberus_admin', 'droits', array('user' => md5($_SESSION['admin']['user']))))
				: null;
				if(empty(self::$droits))
					foreach(self::$navigation as $section => $pages)
						foreach($pages as $page) self::$droits[$page] = true;

			// Vérification de la page
			$title = 'Administration';
			if(!empty(self::$navigation))
			{
				$admin = r::get('admin');
				if(	isset($admin) and
					in_array($admin, self::$droits))

					$title = ($this->arrayLangues) ? l::get('menu-admin-' .$admin) : ucfirst($admin);
			}

			// Affichage de la page
			echo '<div id="admin">';
			$this->adminNavigation();
			$this->content();
			echo '</div>';
		}
	}

	// Charger une page d'admin
	public function content()
	{
		if(isset($_GET['admin']))
		{
			$page = r::get('admin');
			if(f::inclure('cerberus/include/admin.' .$page. '.php')) true;
			elseif(f::inclure('pages/admin-' .$page. '.php')) true;
		}
	}

	// Admin en plusieures langues
	public function defineMultilangue($arrayLangues = null)
	{
		$this->arrayLangues = $arrayLangues;
		if(MULTILANGUE)
		{
			$this->multilangue = config::get('langues', array(config::get('langue_default', 'fr')));
			if(count($this->multilangue) == 1) $this->multilangue = false;
		}
		else $this->multilangue = false;
		return $this->multilangue;
	}

	/*
	########################################
	############# IDENTIFICATION ###########
	########################################
	*/

	// Formulaire d'identification et vérification
	public function adminLogin()
	{
		$adminForm = new forms();
		$adminForm->openFieldset('Identification');
			$adminForm->addText('user', 'Identifiant');
			$adminForm->addPassword('password', 'Mot de passe');
			$adminForm->addSubmit('Connexion');
		$adminForm->closeFieldset();

		// Vérification du formulaire
		if(isset($_POST['user'], $_POST['password']))
		{
			if($this->checkLogin($_POST['user'], $_POST['password']))
			{
				$_SESSION['admin']['user'] = $_POST['user'];
				$_SESSION['admin']['password'] = $_POST['password'];
				$this->granted = true;
			}
			else
			{
				str::display('Les identifiants entrés sont incorrects.', 'error');
				$adminForm->render();
			}
		}
		elseif(isset($_SESSION['admin']['user'], $_SESSION['admin']['password']) and $this->checkLogin($_SESSION['admin']['user'], $_SESSION['admin']['password'])) $this->granted = true;
		else
		{
			str::display('Veuillez entrer votre identifiant et mot de passe.');
			$adminForm->render();
		}
	}

	// Vérification des identifiants
	public function checkLogin($user, $password)
	{
		if(db::connection() and db::is_table('cerberus_admin'))
		{
			$queryQ = db::field('cerberus_admin', 'password', array('user' => md5($user)));
			return (isset($queryQ) && md5($password) == $queryQ);
		}
		else return (md5($user) == $this->loginUser and md5($password) == $this->loginPassword);
	}

	// Recupération de l'identification
	public function accessGranted()
	{
		return $this->granted;
	}

	/*
	########################################
	############## NAVIGATION ##############
	########################################
	*/
	public function adminNavigation()
	{
		echo '<div id="admin-navigation"><h4>Tableau de bord</h4>';

		if(MULTILANGUE and $this->multilangue)
		{
			echo '<div class="btn-group"><button class="btn category">Langue</button>';
			foreach($this->multilangue as $langue)
			{
				$flagState = (l::admin_current() == $langue) ? null : '_off';
				$active = ($langue == l::admin_current()) ? 'btn-inverse' : null;
				echo '<a class="btn ' .$active. '" href="' .url::reload(array('get_admin_langue' => $langue)). '">' .str::img(PATH_CERBERUS.'img/flag-' .$langue.$flagState. '.png', $langue). '</a>';
			}
			echo '</div>';
		}

		echo '<div class="btn-group">
		<button class="btn category">Pages du site</button>';

		// Langue de l'admin

		// Navigation de l'admin
		asort(self::$navigation);
		if(!empty(self::$navigation))
		foreach(self::$navigation as $sections => $pages)
		{
			// Séparation
			if($sections == 'systeme')
				echo '</div>
				<div class="btn-group bottom"><button class="btn category">Pages système</button>';

			// Enumération des liens
			foreach($pages as $titre => $page)
			{
				if(!empty($page) and self::$droits[$page])
				{
					// Texte
					$texteLien = (!is_numeric($titre)) ? $titre : l::getTranslation('menu-admin-'.$page, l::admin_current(), $page, true);
					$thisActive = (isset($_GET['admin']) and $page == $_GET['admin']) ? 'btn-inverse' : null;
					echo '<a class="btn ' .$thisActive. '" href="' .url::rewrite('admin-' .$page). '">' .ucfirst($texteLien). '</a>';
				}
			}
		}
		echo '<a class="btn btn-warning" href="' .url::rewrite('admin', 'logoff'). '">Déconnexion</a></div></div>';
	}

	public function get($variable)
	{
		return self::${$variable};
	}
}
