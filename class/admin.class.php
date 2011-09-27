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
		$this->url = 'index.php';
		$this->modeSQL = function_exists('connectSQL');
		
		if(is_array($arrayLang) and !empty($arrayLang)) 
		{
			$this->arrayLang = $arrayLang;
			$this->multilangue = TRUE;
		}
		else $this->multilangue = FALSE;
	}
	function getFieldsTable()
	{
		return array($this->fields, $this->table);
	}
	
	/* ########################################
	############### NAVIGATION ###############
	######################################## */
	function admin_navigation($navigation)
	{
		echo '<div class="navbar" style="position:relative">';
		
		if($this->multilangue)
		{
			echo '<p style="position: absolute; right: 0; top: -12px">';
			// Langue de l'admin
			foreach($this->arrayLang as $lg)
			{
				$getAdmin = (isset($_GET['admin'])) ? '&admin=' .$_GET['admin'] : '';
				$urlFlag = ($_SESSION['admin']['langue'] == $lg) ? 'flag_' .$lg : 'flag_' .$lg. '_off';
				echo '<a href="' .rewrite('admin', array('adminLangue' => $lg.$getAdmin)). '"><img src="css/' .$urlFlag. '.png" alt="' .$lg. '" /></a> ';
			}
			echo '</p>';
		}
	
		// Navigation de l'admin
		if(!empty($navigation)) foreach($navigation as $key => $value)
		{
			$textLien = ($this->multilangue) ? index('admin-' .$value) : ucfirst($value);
			$thisActive = (isset($_GET['admin']) and $value == $_GET['admin']) ? 'class="hover"' : '';
			echo '<a href="' .rewrite('admin-' .$value). '" ' .$thisActive. '>' .$textLien. '</a>';	
		}
		echo '<a href="' .rewrite('admin', array('logoff')). '">Déconnexion</a></div><br />';
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
		
	/*
	########################################
	############## CONSTRUCT ###############
	######################################## 
	*/
	function build($thisNavigation = '')
	{	
		global $_SESSION;
		global $navigation;
		if(isset($_GET['logoff'])) unset($_SESSION['admin']);
		
		// Ajout des pages par défaut
		$systemPages = array('meta', 'backup');
		$adminNavigation = array_diff($navigation['admin'], array('admin'));
		$thisNavigation = array_merge(beArray($thisNavigation), $adminNavigation, $systemPages);
		 
		$this->adminLogin();
		
		// Variables d'identification
		if($this->granted == TRUE)
		{
			// INTERFACE D'ADMINISTRATION
			$title = 'Administration';
			if(!empty($thisNavigation))
			{
				if
				(
					isset($_GET['admin']) and
					in_array($_GET['admin'], $thisNavigation) and
					(file_exists('pages/admin-' .$_GET['admin']. '.php')
						or in_array($_GET['admin'], $systemPages))
				)
					$title = ($this->multilangue) ? index('admin-' .$_GET['admin']) : ucfirst($_GET['admin']);
			}
			
			echo '<h1>' .$title. '</h1>';
			$this->admin_navigation($thisNavigation);
			
			echo '<div id="admin">';
			if($title != 'Administration')
			{
				if($_GET['admin'] == 'meta') $this->meta();
				elseif($_GET['admin'] == 'backup') $this->backup();
				else include('pages/admin-' .$_GET['admin']. '.php');
			}
			echo '</div>';
		}
	}
	// PAGE META
	function meta()
	{
		$metaAdmin = new AdminClass();
		$metaAdmin->setPage('meta');
		$metaAdmin->createList(array('page'));
		
		// Formulaire
		if(isset($_GET['add']) || isset($_GET['edit']))
		{	
			// Paramètres ajout/modif
			if(isset($_GET['edit']))
			{
				$diffText = 'Modifier';
				$urlAction = 'edit=' .$_GET['edit'];
			}
			else
			{
				$diffText = 'Ajouter';
				$urlAction = 'add';
			}
			
			global $navigation;
			
			foreach($navigation as $key => $value)
				foreach($value as $page) $availablePages[] = $key. '-' .$page;
		
			$form = new form(false, array('action' => rewrite('admin-meta', array($urlAction))));
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
	// BACKUP
	function backup()
	{
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
			
		foreach(glob('./cerberus/cache/sql/*') as $file)  
		{  
			if(is_dir($file))
			{
				$folderDate = str_replace('./cerberus/cache/sql/', '', $file);
				echo 
				'<tr>
				<td>' .$folderDate. '</td>
				<td><a href="' .rewrite('admin-backup', array('load' => $folderDate)). '"><img src="css/load.png" /></a></td>
				<td><a href="' .rewrite('admin-backup', array('delete' => $folderDate)). '"><img src="css/cross.png" /></a></td>
				</tr>';
			}
		}  
		echo '</tbody></table>';
	}
	
	/* ########################################
	########TRAITEMENT DES DONNEES ###########
	######################################## */
	
	function setPage($table, $facultativeFields = array())
	{
		$this->table = $table;
		$this->thisPage = rewrite('admin-' .$_GET['admin']);
		
				
		// Champs facultatifs
		if(isset($facultativeFields) and !empty($facultativeFields) and !is_array($facultativeFields))
			$facultativeFields = array($facultativeFields);
		
		// Récupération du nom des champs
		$this->fields = array_keys(mysqlQuery('SHOW COLUMNS FROM ' .$table));
		$this->index = $this->fields[0];
		
		// AJOUT ET MODIFICATION
		if(isset($_POST['edit'])) 
		{
			// Vérification des champs disponibles
			$emptyFields = array();
			if($this->multilangue == TRUE) $fieldsUpdate['langue'] = $_SESSION['admin']['langue'];
			foreach($_POST as $key => $value)
			{
				if(findString('_annee', $key))
				{
					// Recomposition des champs date
					$originalField = substr($key, 0, -6); 
					$fieldsUpdate[$originalField] = $_POST[$originalField. '_annee']. '-' .$_POST[$originalField. '_mois']. '-' .$_POST[$originalField. '_jour'];
				}
				if(in_array($key, $this->fields))
				{
					if(!$this->is_blank($value)) $fieldsUpdate[$key] = $value;
					else if(!in_array($key, $facultativeFields)) $emptyFields[] = $key;
				}
			}
		
			// Execution de la requête
			if(empty($emptyFields))
			{		
				$uploadImage = $this->uploadImage();
				if($uploadImage != NULL) $fieldsUpdate['path'] = $uploadImage;
				
				if($_POST['edit'] == 'add')
					mysqlQuery(array('INSERT INTO ' .$this->table. ' SET ' .simplode(array('="', '"'), ',', $fieldsUpdate), 'Objet ajouté'));
				else
					mysqlQuery(array('UPDATE ' .$this->table. ' SET ' .simplode(array('="', '"'), ',', $fieldsUpdate). ' WHERE ' .$this->index. '="' .$_POST['edit']. '"', 'Objet modifié'));
			}
			else echo display('Un ou plusieurs champs sont incomplets : ' .implode(', ', $emptyFields));
		}
		// SUPPRESSION
		if(isset($_GET['delete']))
		{
			// Images liées
			if(in_array('path', $this->fields))
			{
				$path = mysqlQuery('SELECT path FROM ' .$this->table .' WHERE ' .$this->index. '="' .$_GET['delete']. '"');
				if(isset($path) and !empty($path)) sunlink('file/' .$this->table. '/' .$path);
			}
			else
			{
				if(file_exists('file/' .$this->table))
				{
					$picExtension = array('jpg', 'jpeg', 'gif', 'png');
					foreach($picExtension as $value)
					{
						$thisFile = $_GET['delete']. '.' .$value;
						sunlink('file/' .$this->table. '/' .$thisFile);
					}
				}
			}
						
			mysqlQuery(array('DELETE FROM ' .$this->table. ' WHERE ' .$this->index. '="' .$_GET['delete']. '"', 'Objet supprimé'));
		}	
		if(isset($_GET['deleteThumb']))
		{
			sunlink('file/' .$this->table. '/' .$_GET['deleteThumb']. '.jpg');
			echo display('Miniature supprimée');
		}
	}
	
	/* 
	########################################
	############### LISTE DES DONNEES ######
	######################################## 
	
	Possibilité de donner une requête manuelle au script 
	via la formulation array(REQUETE => ARRAY(CHAMPS,CHAMPS))
	*/
	function createList($fieldsList, $groupBy = '', $params = '')
	{		
		$manualQuery = (findString('SELECT', key($fieldsList)));
	
		// LISTE DES ENTREES
		echo '<table><thead><tr class="entete">';
		if($manualQuery)
		{
			$thisQuery = key($fieldsList);
			$fieldsList = $fieldsList[$thisQuery];
			if(!is_array($fieldsList)) $fieldsList = explode(',', $fieldsList);
		}
		
		foreach($fieldsList as $key => $value)
		{
			$nomColonne = (is_numeric($key))
			? $value
			: $key;
			echo '<td>' .ucfirst($nomColonne). '</td>';
		}
		echo '<td>Modifier</td><td>Supprimer</td></tr></thead><tbody>';
		
		if(!$manualQuery)
		{
			$availableFields = array_keys(mysqlQuery('DESCRIBE ' .$this->table));
			$newFieldsList = $fieldsList;
			
			array_unshift($newFieldsList, 'id');
			foreach($newFieldsList as $key => $value)
				if(!in_array($value, $availableFields)) unset($newFieldsList[$key]);
				else if(!isset($index)) $index = $value;			
			
			// Multilingue ou non
			$where = ($this->multilangue) 
				? ' WHERE langue="' .$_SESSION['admin']['langue']. '"' 
				: '';
			
			// WHERE
			if(isset($params['WHERE']))
			{
				$where .= (empty($where))
				? ' WHERE ' .$params['WHERE']
				: ' AND ' .$params['WHERE'];
			}
			
			// ORDER BY
			$orderBy = isset($params['ORDER BY'])
				? $params['ORDER BY']
				: $index. ' DESC';
				
			$thisQuery = 'SELECT ' .implode(',', $newFieldsList). ' FROM ' .$this->table.$where. ' ORDER BY ' .$orderBy;
		}
				
		$thisGroup = '';
		$items = mysqlQuery($thisQuery, TRUE);
		if($items) foreach($items as $key => $value)
		{
			if(!empty($groupBy))
			{
				if($thisGroup != $value[$groupBy])
				{
					echo '<tr class="entete"><td colspan="50" class="groupby">' .ucfirst($value[$groupBy]). '</td></tr>';
					$thisGroup = $value[$groupBy];
				}
			}
			
			echo '<tr>';
			if(is_array($value)) foreach($fieldsList as $fname) echo '<td>' .html(str_replace('<br />', ' ', $value[$fname])). '</td>';
			else echo '<td>' .html(str_replace('<br />', ' ', $value)). '</td>';
			echo '<td><a href="' .rewrite('admin-' .$_GET['admin'], array('edit' => $key)). '"><img src="css/pencil.png" /></a></td>
			<td><a href="' .rewrite('admin-' .$_GET['page'], array('delete' => $key)). '"><img src="css/cross.png" /></a></td></tr>';
		}
		echo '<tr class="additem"><td colspan="50"><a href="' .$this->thisPage. '&add">Ajouter un élément</a></td></tr></tbody></table><br /><br />';
	}
		
	/*
	########################################
	######## FONCTIONS FORMULAIRES #########
	########################################
	*/
		
	// Détermine si le formulaire est en mode ajout ou modif
	function addOrEdit()
	{
		if(isset($_GET['edit']))
		{
			$diff = $_GET['edit'];
			$diffText = 'Modifier';
			$urlAction = 'edit=' .$_GET['edit'];
		}
		else
		{
			$diff = 'add';
			$diffText = 'Ajouter';
			$urlAction = 'add';
		}	
		return array($diffText, $urlAction, $diff);
	}
	
	// Vérifie si un champ est véritablement nul
	function is_blank($value) 
	{
		return empty($value) && !is_numeric($value);
	}
		
	/* 
	########################################
	############## ENVOI D'IMAGES ##########
	########################################
	*/
	function uploadImage($field = 'thumb')
	{
		$GLOBALS['cerberus']->injectModule('normalize', 'filecat');
		
		if(isset($_FILES[$field]['name']) and !empty($_FILES[$field]['name']))
		{
			/*
			Mode de sauvegarde de l'image
			# TABLE - présence d'une tableur sœur TABLE_thumbs contenant les images.
				Plusieurs image
			# PATH - Champ path dans la table même stockant l'url de l'image
				Une image
			# ID - L'image prend l'id de l'entrée pour reconnaissance
				Une image
			*/
			if(in_array($this->table. '_thumb', mysqlQuery('SHOW TABLES'))) $storageMode = 'table';
			elseif(array_key_exists('path', mysqlQuery('SHOW COLUMNS FROM ' .$this->table))) $storageMode = 'path';
			else $storageMode = 'id';

			// Erreurs basiques
			$errorDisplay = '';
			$extension = strtolower(substr(strrchr($_FILES[$field]['name'], '.'), 1));
			if($_FILES[$field]['error'] != 0) $errorDisplay = 'Une erreur est survenue lors du transfert.';
			if(filecat($extension) != 'image') $errorDisplay .= '<br />L\'extension du fichier n\'est pas valide';
					
			// Si aucune erreur
			if(empty($errorDisplay))
			{	
				$autoIncrement = mysql_fetch_array(mysql_query('SHOW TABLE STATUS LIKE "' .$this->table. '"'));
				$lastID = ($_POST['edit'] == 'add')
					? $autoIncrement['Auto_increment']
					: $_POST['edit'];
			
				switch($storageMode)
				{
					case 'table':
						$file = explode('.', $_FILES[$field]['name']);
						$file = normalize($file[0]). '-' .md5(randomString()). '.' .$extension;
						$test = mysqlQuery('SHOW COLUMNS FROM ' .$this->table. '_thumb');
						mysqlQuery(array('INSERT INTO ' .$this->table. '_thumb SET path="' .$file. '", id_' .$this->table. '="' .$lastID. '"'));
						break;
						
					case 'path':
						$path = mysqlQuery('SELECT path FROM ' .$this->table .' WHERE ' .$this->index. '="' .$lastID. '"');
						if(isset($path) and !empty($path)) sunlink('file/' .$this->table. '/' .$path);
						sunlink('file/' .$this->table. '/' .$lastID. '.jpg');
						$file = $lastID. '-' .md5(randomString()). '.' .$extension;
						break;
						
					default:
						$file = $lastID. '.' .$extension;
						break;
				}
				
				// Sauvegarde de l'image
				$resultat = move_uploaded_file($_FILES[$field]['tmp_name'], 'file/' .$this->table. '/' .$file);
				if($resultat)
				{
					echo display('Image ajoutée au serveur');
					if($storageMode == 'path') return $file;
					else return NULL;
				}
				else echo display('Une erreur est survenue lors du transfert.');
			}
			else echo display($errorDisplay);
		}
	}
}
?>