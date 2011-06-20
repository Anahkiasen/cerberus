<?php
class AdminClass
{
	public $arrayLang;
	public $navigAdmin;
	public $facultativeFields;
	
	private $thisPage;
	private $result;
	private $fields;
	private $table;
	private $fieldsDisplay;
	private $manualQuery;	
	
	function __construct($arrayLang = '')
	{
		if(is_array($arrayLang) and !empty($arrayLang)) 
		{
			$this->arrayLang = $arrayLang;
			$this->multilangue = TRUE;
		}
		else $this->multilangue = FALSE;
	}
	
	/* ########################################
	############### IDENTIFICATION ###########
	######################################## */
	function admin_login()
	{
		$admin_form = '<form method="post">
		<fieldset class="login"><legend>Identification</legend>
		<dl><dt>Identifiant</dt><dd><input type="text" name="user" /></dd></dl>
		<dl><dt>Mot de passe</dt><dd><input type="password" name="password" /></dd></dl>
		<dl><dd class="unirow" style="text-align:left"><input type="submit" value="Valider" /></dd></dl>
		</fieldset></form>';
		
		// Vérification du formulaire
		if(isset($_POST['user']) && isset($_POST['password']))
		{
			$queryQ = mysqlQuery('SELECT password FROM admin WHERE user="' .md5($_POST['user']). '"');
			if(isset($queryQ) && (md5($_POST['password']) == $queryQ))
			{
				$_SESSION['admin']['user'] = $_POST['user'];
				$_SESSION['admin']['password'] = $_POST['password'];
				$this->result = true;
			}
			else echo '<p class="infoblock">Les identifiants entrés sont incorrects.</p>' .$admin_form;
		}
		else echo '<p class="infoblock">Veuillez entrer votre identifiant et mot de passe.</p>' .$admin_form;
	}
	
	/* ########################################
	############### NAVIGATION ###############
	######################################## */
	function admin_navigation()
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
				echo '<a href="index.php?page=admin&adminLangue=' .$lg.$getAdmin. '"><img src="css/' .$urlFlag. '.png" alt="' .$lg. '" /></a> ';
			}
			echo '</p>';
			
			foreach($this->navigAdmin as $value) echo '<a href="index.php?page=admin&admin=' .$value. '">' .index('admin-' .$value). '</a>';
		}
	
		// Navigation de l'admin
		foreach($this->navigAdmin as $key => $value)
		{
			$thisActive = (isset($_GET['admin']) and $value == $_GET['admin']) ? 'class="hover"' : '';
			echo '<a href="index.php?page=admin&admin=' .$value. '" ' .$thisActive. '>' .ucfirst($value). '</a>';	
		}
		echo '<a href="index.php?logoff">Déconnexion</a></div><br />';
	}
	
	/* ########################################
	############### CONSTRUCT #################
	######################################## */
	function build($navigAdmin)
	{	
		global $_SESSION;
		$this->navigAdmin = $navigAdmin;		
		
		// Vérification du compte actuel
		if(isset($_SESSION['admin']['user'], $_SESSION['admin']['password']))
		{
			$user = $_SESSION['admin']['user'];
			$mdp = $_SESSION['admin']['password'];
			
			$queryQ = mysqlQuery('SELECT password FROM admin WHERE user="' .md5($user). '"');
			if(isset($queryQ) && (md5($mdp) == $queryQ)) $this->result = true;
			else $this->result = false;
		}
		else $this->result = false;
		
		// Si le compte n'est pas valide
		if($this->result == false) $this->admin_login();
		if($this->result == true)
		{
			// INTERFACE D'ADMINISTRATION
			if(isset($_GET['admin']) && in_array($_GET['admin'], $this->navigAdmin) && file_exists('include/page-admin-' .$_GET['admin']. '.php'))
			{
				if($this->multilangue) $title = index('page-admin-' .$_GET['admin']);
				else $title = ucfirst($_GET['admin']);
			}
			else $title = 'Administration';
			
			echo '<h1>' .$title. '</h1>';
			$this->admin_navigation();
			
			echo '<div id="admin">';
			if($title != 'Administration') include('include/page-admin-' .$_GET['admin']. '.php');
			echo '</div>';
		}
	}
	
	/* ########################################
	############### LISTE DES DONNEES ########
	######################################## */
	function createList($fieldsList, $groupBy = '')
	{	
		if(!isset($this->manualQuery)) $this->manualQuery = FALSE;
	
		// LISTE DES ENTREES
		echo '<table><thead><tr class="entete">';
		if($this->manualQuery == TRUE)
		{
			$thisQuery = key($fieldsList);
			$fieldsList = explode(',', $fieldsList[$thisQuery]);
		}
		foreach($fieldsList as $value) echo '<td>' .ucfirst($value). '</td>';
		echo '<td>Modifier</td><td>Supprimer</td></tr></thead><tbody>';
		
		if($this->manualQuery == FALSE)
		{
			// Multilingue ou non
			$isLang = ($this->multilangue) 
				? ' WHERE langue="' .$_SESSION['admin']['langue']. '"' 
				: '';
			$thisQuery = 'SELECT id,' .implode(',', $fieldsList). ' FROM ' .$this->table.$isLang. ' ORDER BY id DESC';
		}
		
		$thisGroup = '';
		$items = mysqlQuery($thisQuery);
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
			foreach($fieldsList as $fname) echo '<td>' .html(str_replace('<br />', ' ', $value[$fname])). '</td>';
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
	function setPage($table)
	{
		$this->table = $table;
		$this->thisPage = 'index.php?page=admin&admin=' .$_GET['admin'];
		
		// Champs facultatifs
		if(isset($this->facultativeFields) and !empty($this->facultativeFields)) if(!is_array($this->facultativeFields)) $this->facultativeFields = array($this->facultativeFields);
		
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
					if(!empty($value)) $fieldsUpdate[] = $key. '="' .bdd($value). '"';
					else if(!in_array($key, $this->facultativeFields)) $emptyFields[] = $key;
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
					echo '<p class="infoblock">Objet ajouté</p>';
				}
				else
				{
					$this->uploadImage('thumb', $_POST['edit']);
					mysql_query('UPDATE ' .$this->table. ' SET ' .implode(',', $fieldsUpdate). ' WHERE id=' .$_POST['edit']) or die(mysql_error());
					echo '<p class="infoblock">Objet modifié</p>';
				}
			}
			else echo '<p class="infoblock">Un ou plusieurs champs sont incomplets : ' .implode(', ', $emptyFields). '</p>';
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
			echo '<p class="infoblock">Objet supprimé</p>';
		}
	
	}
	
	/* ########################################
	############### ENVOI D'IMAGES ###########
	######################################## */
	function uploadImage($field, $name)
	{
		if(isset($_FILES[$field]['name']))
		{
			$fileErreur = '';
			$extension_upload = strtolower(substr(strrchr($_FILES[$field]['name'], '.'), 1));
			if($_FILES[$field]['error'] != 0) $fileErreur .= '<br />Une erreur est survenue lors du transfert.';
			if(!in_array($extension_upload, array('jpeg', 'jpg', 'gif', 'png'))) $fileErreur .= 'L\'extension du fichier n\'est pas valide';
					
			if($fileErreur == '')
			{	
				$file = $name. '.' .$extension_upload;
				$resultat = move_uploaded_file($_FILES[$field]['tmp_name'], 'file/' .$this->table. '/' .$file);
				if($resultat) echo '<p class="infoblock">Image ajoutée au serveur</p>';
			}
		}
	}
}
?>