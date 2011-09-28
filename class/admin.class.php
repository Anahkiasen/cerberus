<?php
class AdminPage extends AdminSetup
{		
	private $arrayLangues;
	private $multilangue;
	
	function __construct()
	{		
		$this->modeSQL = function_exists('connectSQL');
		$this->defineMultilangue();
	}
	function getFieldsTable()
	{
		return array($this->fields, $this->table);
	}
	
	/*
	########################################
	############## MISE EN PLACE ###########
	######################################## 
	*/
	
	function setPage($table, $facultativeFields = array())
	{
		$this->table = $table;
		$this->getEdit = @$_GET['edit_' .$this->table];
		$this->getAdd = @$_GET['add_' .$this->table];

		// Champs facultatifs
		$facultativeFields = beArray($facultativeFields);
		
		// Récupération du nom des champs
		$this->fields = array_keys(mysqlQuery('SHOW COLUMNS FROM ' .$table));
		$this->index = $this->fields[0];
		
		// AJOUT ET MODIFICATION
		if(isset($_POST['edit'])) 
		{
			// Vérification des champs disponibles
			$emptyFields = array();
			if($this->multilangue) $fieldsUpdate['langue'] = $_SESSION['admin']['langue'];
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
		if(isset($_GET['delete_' .$this->table]))
		{
			// Images liées
			if(in_array('path', $this->fields))
			{
				$path = mysqlQuery('SELECT path FROM ' .$this->table .' WHERE ' .$this->index. '="' .$_GET['delete_' .$this->table]. '"');
				if(isset($path) and !empty($path)) sunlink('file/' .$this->table. '/' .$path);
			}
			else
			{
				if(file_exists('file/' .$this->table))
				{
					$picExtension = array('jpg', 'jpeg', 'gif', 'png');
					foreach($picExtension as $value)
					{
						$thisFile = $_GET['delete_' .$this->table]. '.' .$value;
						sunlink('file/' .$this->table. '/' .$thisFile);
					}
				}
			}
						
			mysqlQuery(array('DELETE FROM ' .$this->table. ' WHERE ' .$this->index. '="' .$_GET['delete_' .$this->table]. '"', 'Objet supprimé'));
		}	
		if(isset($_GET['deleteThumb']))
		{
			sunlink('file/' .$this->table. '/' .$_GET['deleteThumb']. '.jpg');
			echo display('Miniature supprimée');
		}
	}
	
	/*
	########################################
	########## TABLEAU DES DONNES ##########
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
			echo '<td><a href="' .rewrite('admin-' .$_GET['admin'], array('edit_' .$this->table => $key)). '"><img src="css/pencil.png" /></a></td>
			<td><a href="' .rewrite('admin-' .$_GET['page'], array('delete_' .$this->table => $key)). '"><img src="css/cross.png" /></a></td></tr>';
		}
		echo '<tr class="additem"><td colspan="50"><a href="' .rewrite('admin-' .$this->table, 'add_' .$this->table). '">Ajouter un élément</a></td></tr></tbody></table><br /><br />';
	}
				
	/*
	########################################
	######## FONCTIONS FORMULAIRES #########
	########################################
	*/
		
	// Détermine si le formulaire est en mode ajout ou modif
	function addOrEdit($formulaire = '')
	{
		if(!empty($formulaire))
		{
			if(isset($this->getEdit) or isset($this->getAdd)) return $formulaire;
		}
		else
		{
			if(isset($this->getEdit))
			{
				$diff = $this->getEdit;
				$diffText = 'Modifier';
				$urlAction = 'edit=' .$this->getEdit;
			}
			else
			{
				$diff = 'add';
				$diffText = 'Ajouter';
				$urlAction = 'add';
			}	
			return array($diffText, $urlAction, $diff);
		}
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