<?php
class AdminSetup
{
	// Options
	private $modeSQL; // Utilise une BDD ou pas
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
		
		$this->modeSQL = function_exists('connectSQL');
		$this->defineMultilangue();
		
		// Ajout des pages par défaut
		$systemPages = array('news', 'meta', 'images', 'backup');
		$adminNavigation = array_diff($navigation['admin'], array('admin'));
		$thisNavigation = array_merge(beArray($customNavigation), $adminNavigation, $systemPages);
	
		// Identification	 
 		if(isset($_GET['logoff'])) unset($_SESSION['admin']);
		$this->adminLogin();
		
		if($this->granted)
		{
			// Vérification de la page
			$title = 'Administration';
			if(!empty($thisNavigation))
			{
				if(	isset($_GET['admin']) and
					in_array($_GET['admin'], $thisNavigation) and
					(file_exists('pages/admin-' .$_GET['admin']. '.php')
						or in_array($_GET['admin'], $systemPages)))
						
					$title = ($this->arrayLangues) ? index('admin-' .$_GET['admin']) : ucfirst($_GET['admin']);
			}
			
			// Navigation
			echo '<h1>' .$title. '</h1>';
			$this->admin_navigation($thisNavigation);
			
			echo '<div id="admin">';
			if($title != 'Administration')
			{
				// Chargement de la page
				if($_GET['admin'] == 'meta') $this->meta();
				elseif($_GET['admin'] == 'news') $this->news();
				elseif($_GET['admin'] == 'backup') $this->backup();
				else include('pages/admin-' .$_GET['admin']. '.php');
			}
			echo '</div>';
		}
	}
	
	// Admin en plusieures langues
	function defineMultilangue($arrayLangues = NULL)
	{		
		global $index;
		
		$this->arrayLangues = $arrayLangues;
		if(MULTILANGUE)
		{
			$this->multilangue = array_diff(array_keys($index), array('mail', 'http'));
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
		if($this->modeSQL)
		{
			$queryQ = mysqlQuery('SELECT password FROM admin WHERE user="' .md5($user). '"');
			return (isset($queryQ) && md5($password) == $queryQ);
		}
		elseif(!$this->modeSQL and isset($this->loginUser)) return (md5($user) == $this->loginUser and md5($password) == $this->loginPass);
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
		
		if(MULTILANGUE)
		{
			echo '<p style="position: absolute; right: 5px; top: -7px">';
			// Langue de l'admin
			foreach($this->multilangue as $langue)
			{
				$getAdmin = (isset($_GET['admin'])) ? '&admin=' .$_GET['admin'] : '';
				$urlFlag = ($_SESSION['admin']['langue'] == $langue) ? 'flag_' .$langue : 'flag_' .$langue. '_off';
				echo '<a href="' .rewrite('admin', array('adminLangue' => $langue.$getAdmin)). '"><img src="assets/css/' .$urlFlag. '.png" alt="' .$langue. '" /></a> ';
			}
			echo '</p>';
		}
	
		// Navigation de l'admin
		if(!empty($navigation)) foreach($navigation as $key => $value)
		{
			//if($value == 'news') echo '<br /><br />';
			$textLien = ($this->arrayLangues) ? index('admin-' .$value) : ucfirst($value);
			$thisActive = (isset($_GET['admin']) and $value == $_GET['admin']) ? 'class="hover"' : '';
			echo '<a href="' .rewrite('admin-' .$value). '" ' .$thisActive. '>' .$textLien. '</a>';	
		}
		echo 
		'<a href="' .rewrite('admin', 'logoff'). '">Déconnexion</a>
		</div><br />';
	}
	
	/*
	########################################
	############## PAGES SYSTEME ###########
	######################################## 
	*/
		
	// PAGE META
	function meta()
	{
		$metaAdmin = new AdminPage();
		$metaAdmin->setPage('meta', 'lien');
		$metaAdmin->createList(array('page'));
		$metaAdmin->addOrEdit($diff, $diffText, $urlAction);
		// Formulaire
		if(isset($_GET['add_meta']) || isset($_GET['edit_meta']))
		{				
			global $navigation;
			
			// Liste des pages
			foreach($navigation as $key => $value)
				foreach($value as $page) $availablePages[] = $key. '-' .$page;
		
			// Formulaire
			$form = new form(false, array('action' => rewrite('admin-meta', $urlAction)));
			$select = new select();
			$form->getValues($metaAdmin->getFieldsTable());
			
			$form->openFieldset($diffText. ' des données meta');
				$select->newSelect('page', 'Identifiant de la page'); 
					$select->appendList($availablePages);
					$form->insertText($select);
				$form->addText('titre', 'Titre de la page');
				$form->addText('lien', 'URL de la page');
				$form->addTextarea('description', 'Description de la page', '', array('underfield' => true));
				$form->addEdit();
				$form->addSubmit($diffText);
			$form->closeFieldset();
			
			echo $form;
		}
	}
	
	// NEWS
	function news()
	{
		$GLOBALS['cerberus']->injectModule('news');
		$news = new getNews();
		$news->adminNews();
	}
	
	// BACKUP
	function backup()
	{
		// Suppression et chargement
		if(isset($_GET['delete']))
		{
			$path = 'cerberus/cache/sql/' .$_GET['delete']. '/';
			if(file_exists($path))
			{
				sunlink($path);
				echo display('La sauvegarde du ' .$_GET['delete']. ' a bien été supprimée');
			}
			else echo display('Sauvegarde introuvable');
		}
		if(isset($_GET['load']))
		{
			include('cerberus/cache/conf.php');
			foreach(glob('cerberus/cache/sql/' .$_GET['load']. '/*.sql') as $file)
				$fichier = $file;
				
			multiQuery(file_get_contents($fichier), array($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB));
			echo display('La sauvegarde du ' .$_GET['load']. ' a bien été chargée');
		}
	
		echo '<p>Ci-dessous se trouve la liste des sauvegardes journalières.</p>
		<table>
			<thead>
				<tr class="entete">
					<td>Date</td>
					<td>Charger</td>
					<td>Supprimer</td>
				</tr>
			</thead>
			<tbody>';
			
		// Liste des sauvegardes
		foreach(glob('./cerberus/cache/sql/*') as $file)  
		{  
			if(is_dir($file))
			{
				$folderDate = str_replace('./cerberus/cache/sql/', '', $file);
				echo 
				'<tr>
				<td>' .$folderDate. '</td>
				<td><a href="' .rewrite('admin-backup', array('load' => $folderDate)). '"><img src="assets/css/load.png" /></a></td>
				<td><a href="' .rewrite('admin-backup', array('delete' => $folderDate)). '"><img src="assets/css/delete.png" /></a></td>
				</tr>';
			}
		}  
		echo '</tbody></table>';
	}
}
?>