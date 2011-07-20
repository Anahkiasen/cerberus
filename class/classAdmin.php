<?php
class AdminClass
{
	public $navigAdmin;
	
	private $fields; // Liste des champs
	private $table; // Table actuelle
	private $tableThumb; // Existence d'une table des images
	
	// Login
	private $loginAdmin;
	private $loginPass;
	private $granted = FALSE;
	
	// Options
	private $arrayLang;
	private $multilangue;
		
	function __construct($arrayLang = '')
	{
		global $cerberus;
		$this->url = $cerberus->url();
		$this->modeSQL = function_exists('connectSQL');
		
		if(is_array($arrayLang) and !empty($arrayLang)) 
		{
			$this->arrayLang = $arrayLang;
			$this->multilangue = TRUE;
		}
		else $this->multilangue = FALSE;
	}
	
	/* ########################################
	############### NAVIGATION ###############
	######################################## */
	function admin_navigation($navigation)
	{
		echo '<div id="navbar" style="position:relative">';
		
		if($this->multilangue)
		{
			echo '<p style="position: absolute; right: 0; top: -12px">';
			// Langue de l'admin
			foreach($this->arrayLang as $lg)
			{
				$getAdmin = (isset($_GET['admin'])) ? '&admin=' .$_GET['admin'] : '';
				$urlFlag = ($_SESSION['admin']['langue'] == $lg) ? 'flag_' .$lg : 'flag_' .$lg. '_off';
				echo '<a href="' .$this->url. '?page=admin&adminLangue=' .$lg.$getAdmin. '"><img src="css/' .$urlFlag. '.png" alt="' .$lg. '" /></a> ';
			}
			echo '</p>';
		}
	
		// Navigation de l'admin
		if(!empty($navigation)) foreach($navigation as $key => $value)
		{
			$textLien = ($this->multilangue) ? index('admin-' .$value) : ucfirst($value);
			$thisActive = (isset($_GET['admin']) and $value == $_GET['admin']) ? 'class="hover"' : '';
			echo '<a href="' .$this->url. '?page=admin&admin=' .$value. '" ' .$thisActive. '>' .$textLien. '</a>';	
		}
		echo '<a href="' .$this->url. '?page=admin&logoff">Déconnexion</a></div><br />';
	}
	
	/* ########################################
	############### IDENTIFICATION ###########
	######################################## */
	
	// Formulaire d'identification et vérification
	function adminLogin()
	{
		$admin_form = '<form method="post">
		<fieldset class="login"><legend>Identification</legend>
		<dl><dt>Identifiant</dt><dd><input type="text" name="user" /></dd></dl>
		<dl><dt>Mot de passe</dt><dd><input type="password" name="password" /></dd></dl>
		<dl class="submit"><dd><p style="text-align:center"><input type="submit" value="Connexion" /></p></dd> 
	</dl>
		</fieldset></form>';
		
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
		if($this->modeSQL == TRUE)
		{
			$queryQ = mysqlQuery('SELECT password FROM admin WHERE user="' .md5($user). '"');
			return (isset($queryQ) && md5($password) == $queryQ);
		}
		elseif($this->modeSQL == FALSE and isset($this->loginAdmin)) return (md5($user) == $this->loginAdmin and md5($password) == $this->loginPass);
		else return FALSE;
	}
	
	// Paramétrage d'identifiants manuels
	function setLogin($user, $password = '')
	{
		$this->loginAdmin = $user;
		$this->loginPass = (!empty($password)) ? $password : $user;
	}
	
	// Recupération de l'identification
	function accessGranted()
	{
		return $this->granted;
	}
		
	/* ########################################
	############### CONSTRUCT #################
	######################################## */
	function build($thisNavigation = '')
	{	
		global $_SESSION;
		if(isset($_GET['logoff'])) unset($_SESSION['admin']);
		 
		$this->adminLogin();
		
		// Variables d'identification
		if($this->granted == TRUE)
		{
			// INTERFACE D'ADMINISTRATION
			$title = 'Administration';
			if(!empty($thisNavigation))
			{
				if(isset($_GET['admin']) && in_array($_GET['admin'], $thisNavigation) && file_exists('pages/admin-' .$_GET['admin']. '.php'))
					$title = ($this->multilangue) ? index('admin-' .$_GET['admin']) : ucfirst($_GET['admin']);
			}
			
			echo '<h1>' .$title. '</h1>';
			$this->admin_navigation($thisNavigation);
			
			echo '<div id="admin">';
			if($title != 'Administration') include('pages/admin-' .$_GET['admin']. '.php');
			echo '</div>';
		}
	}
	
	/* ########################################
	############### LISTE DES DONNEES ########
	######################################## */
	function createList($fieldsList, $groupBy = '')
	{	
		if(findString('SELECT', key($fieldsList))) $manualQuery = TRUE;
		else $manualQuery = FALSE;
	
		// LISTE DES ENTREES
		echo '<table><thead><tr class="entete">';
		if($manualQuery == TRUE)
		{
			$thisQuery = key($fieldsList);
			$fieldsList = explode(',', $fieldsList[$thisQuery]);
		}
		foreach($fieldsList as $value) echo '<td>' .ucfirst($value). '</td>';
		echo '<td>Modifier</td><td>Supprimer</td></tr></thead><tbody>';
		
		if($manualQuery == FALSE)
		{
			// Multilingue ou non
			$isLang = ($this->multilangue) 
				? ' WHERE langue="' .$_SESSION['admin']['langue']. '"' 
				: '';
			$thisQuery = 'SELECT id,' .implode(',', $fieldsList). ' FROM ' .$this->table.$isLang. ' ORDER BY id DESC';
		}
		
		$thisGroup = '';
		$items = mysqlQuery($thisQuery, TRUE);
		if($items) foreach($items as $key => $value)
		{
			if(!empty($groupBy))
			{
				if($thisGroup != $value[$groupBy])
				{
					echo '<tr class="entete"><td colspan="50">' .$value[$groupBy]. '</td></tr>';
					$thisGroup = $value[$groupBy];
				}
			}
			
			echo '<tr>';
			if(is_array($value)) foreach($fieldsList as $fname) echo '<td>' .html(str_replace('<br />', ' ', $value[$fname])). '</td>';
			else echo '<td>' .html(str_replace('<br />', ' ', $value)). '</td>';
			echo '<td><a href="' .$this->thisPage. '&edit=' .$key. '"><img src="css/pencil.png" /></a></td>
			<td><a href="' .$this->thisPage. '&delete=' .$key. '"><img src="css/cross.png" /></a></td></tr>';
		}
		echo '<tr class="additem"><td colspan="50"><a href="' .$this->thisPage. '&add">Ajouter un élément</a></td></tr></tbody></table><br /><br />';
	}
	
	/* ########################################
	############### FORMATTAGE POST ###########
	######################################## */
	function formValues()
	{
		if(isset($_GET['edit']))
		{
			$modif = mysql_fetch_assoc(mysql_query('SELECT ' .implode(',', $this->fields). ' FROM ' .$this->table. ' WHERE id=' .$_GET['edit']));
			foreach($this->fields as $value) $post[$value] = html($modif[$value]); 
		}
		else foreach($this->fields as $value) $post[$value] = '';
		
		if(isset($_POST)) foreach($this->fields as $value)
			if(isset($_POST[$value]) && !empty($_POST[$value])) $post[$value] = html($_POST[$value]);
			
		return $post;
	}
	
	/* ########################################
	########TRAITEMENT DES DONNEES ###########
	######################################## */
	function setPage($table, $facultativeFields = array())
	{
		$this->table = $table;
		$this->thisPage = '' .$this->url. '?page=admin&admin=' .$_GET['admin'];
				
		// Champs facultatifs
		if(isset($facultativeFields) and !empty($facultativeFields)) if(!is_array($facultativeFields)) $facultativeFields = array($facultativeFields);
		
		// Récupération du nom des champs
		$querySQL = mysql_query('SHOW COLUMNS FROM ' .$table);
		while($fieldname = mysql_fetch_assoc($querySQL)) $this->fields[] = $fieldname['Field'];
		
		// AJOUT ET MODIFICATION
		if(isset($_POST['edit'])) 
		{
			// Vérification des champs disponibles
			$emptyFields = '';
			foreach($_POST as $key => $value)
			{
				if(in_array($key, $this->fields))
				{
					if(!is_blank($value)) $fieldsUpdate[] = $key. '="' .bdd($value). '"';
					else if(!in_array($key, $facultativeFields)) $emptyFields[] = $key;
				}
			}
			if($this->multilangue == TRUE) $fieldsUpdate[] = 'langue="' .$_SESSION['admin']['langue']. '"';
		
			// Execution de la requête
			if($emptyFields == '')
			{
				if($_POST['edit'] == 'add')
				{
					$this->uploadImage('thumb', getLastID($this->table));
					mysql_query('INSERT INTO ' .$this->table. ' SET ' .implode(',', $fieldsUpdate)) or die(mysql_error());
					echo display('Objet ajouté');
				}
				else
				{
					$this->uploadImage('thumb', $_POST['edit']);
					mysql_query('UPDATE ' .$this->table. ' SET ' .implode(',', $fieldsUpdate). ' WHERE id=' .$_POST['edit']) or die(mysql_error());
					echo display('Objet modifié');
				}
			}
			else echo display('Un ou plusieurs champs sont incomplets : ' .implode(', ', $emptyFields));
		}
		// SUPPRESSION
		if(isset($_GET['delete']))
		{
			$picExtension = array('jpg', 'jpeg', 'gif', 'png');
			foreach($picExtension as $value)
			{
				$thisFile = $_GET['delete']. '.' .$value;
				if(file_exists('file/' .$this->table. '/' .$thisFile)) unlink('file/' .$this->table. '/' .$thisFile);
				if(file_exists('file/' .$this->table. '/thumb/' .$thisFile)) unlink('file/' .$this->table. '/thumb/' .$thisFile);
			}
			
			mysql_query('DELETE FROM ' .$this->table. ' WHERE id=' .$_GET['delete']);
			echo display('Objet supprimé');
		}
	
	}
	
	/* ########################################
	############### ENVOI D'IMAGES ###########
	######################################## */
	function uploadImage($field, $name)
	{
		if(!isset($this->tableThumb)) $this->tableThumb = (in_array($this->table. '_thumb', mysqlQuery('SHOW TABLES')));
	
		if(isset($_FILES[$field]['name']))
		{
			$fileErreur = '';
			$extension_upload = strtolower(substr(strrchr($_FILES[$field]['name'], '.'), 1));
			if($_FILES[$field]['error'] != 0) $fileErreur .= '<br />Une erreur est survenue lors du transfert.';
			if(!in_array($extension_upload, array('jpeg', 'jpg', 'gif', 'png'))) $fileErreur .= 'L\'extension du fichier n\'est pas valide';
					
			if($fileErreur == '')
			{	
				if($this->tableThumb == TRUE)
				{
					$futureID = getLastID($this->table. '_thumb');
					$file = $futureID. '_' .normalize($_FILES[$field]['name']);
					mysql_query('INSERT INTO ' .$this->table. '_thumb VALUES("", "' .$name. '", "' .$file. '")');
				}
				else $file = $name. '.' .$extension_upload;

				$resultat = move_uploaded_file($_FILES[$field]['tmp_name'], 'file/' .$this->table. '/' .$file);
				if($resultat) echo display('Image ajoutée au serveur');
			}
		}
	}
}
?>