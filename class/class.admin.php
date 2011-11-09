<?php
class AdminPage extends AdminSetup
{	
	// Options
	private $modeSQL; // Site avec BDD
	
	// Table concernée
	private $table;
	private $getEdit;
	private $getAdd; 
	private $index; // Index de la table
	private $fields; // Liste des champs
	
	// Champs supplémentaires
	private $tableRows =
		array(
		'edit' => 'Modifier', 
		'delete' => 'Supprimer');
	
	function __construct()
	{		
		$this->modeSQL = db::connection();
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
		// Information sur la table
		$this->table = $table;
		$this->getEdit = get('edit_' .$this->table, NULL);
		$this->getAdd = get('add_' .$this->table, NULL);

		// Champs de mise à jour
		$fieldsUpdate = array();
		$facultativeFields = a::beArray($facultativeFields);
		
		// Récupération du nom des champs
		$this->fields = db::fields($table);
		$this->index = a::get($this->fields, 0, 'id');
		
		
		// AJOUT ET MODIFICATION
		if(isset($_POST['edit'])) 
		{
			// Vérification des champs disponibles
			$emptyFields = array();
			if(MULTILANGUE and in_array('langue', db::fields($this->table))) $fieldsUpdate['langue'] = $_SESSION['admin']['langue'];
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
				if($_POST['edit'] == 'add')
				{
					db::insert($this->table, $fieldsUpdate);
					prompt('Objet ajouté');
				}
				else
				{
					db::update($this->table, $fieldsUpdate, array($this->index => $_POST['edit']));
					prompt('Objet modifié');
				}
				$uploadImage = $this->uploadImage();
			}
			else prompt('Un ou plusieurs champs sont incomplets : ' .implode(', ', $emptyFields));
		}
		
		// SUPPRESSION
		if(isset($_GET['delete_' .$this->table]))
		{
			// Images liées
			if(in_array('path', $this->fields))
			{
				$path = db::field($this->table, 'path', array($this->index => $_GET['delete_' .$this->table]));
				if(isset($path) and !empty($path)) sunlink('assets/file/' .$this->table. '/' .$path);
			}
			else
			{
				if(file_exists('assets/file/' .$this->table))
				{
					$picExtension = array('jpg', 'jpeg', 'gif', 'png');
					foreach($picExtension as $value)
					{
						$thisFile = $_GET['delete_' .$this->table]. '.' .$value;
						sunlink('assets/file/' .$this->table. '/' .$thisFile);
					}
				}
			}
						
			db::delete($this->table, array($this->index => $_GET['delete_' .$this->table]));
			prompt('Objet supprimé');
		}	
		if(isset($_GET['deleteThumb']))
		{
			$image = $this->getImage($_GET['deleteThumb']);
			if(sunlink('assets/file/' .$this->table. '/' .$image)) prompt('Miniature supprimée');
			else prompt('Miniature introuvable');
		}
	}
	
	/*
	########################################
	########## TABLEAU DES DONNEES #########
	######################################## 
	
	Possibilité de donner une requête manuelle au script 
	via la formulation array(REQUETE => ARRAY(CHAMPS,CHAMPS))
	
	$manualQuery permet de 
	
	DIVIDE
	
	*/
	
	function addRow($function, $name, $type = 'link')
	{
		if($type == 'link') $this->tableRows = array($function => $name) + $this->tableRows;
	}
	function createList($fieldsDisplay, $manualQuery = NULL)
	{		
		echo '<table><thead><tr>';
		
		/* ######## EN-TÊTE ########## */
		
		// Colonnes principales
		foreach($fieldsDisplay as $key => $value)
		{
			$nomColonne = (is_numeric($key))
				? $value
				: $key;
			echo '<td>' .ucfirst($nomColonne). '</td>';
		}
		
		// Colonnes gestion
		if(isset($this->tableRows))
			foreach($this->tableRows as $function => $name)
				echo '<td>' .$name. '</td>';
				
		echo '</tr></thead><tbody>';
		
		/* ######## CONSTRUCTION DE LA REQUÊTE ########## */
		
		// Liste des champs
		$availableFields = db::fields($this->table);
		$fieldsQuery = $fieldsDisplay;
		
		// Définition de l'index et des champs erronés
		array_unshift($fieldsQuery, 'id');
		foreach($fieldsQuery as $key => $value)
			if(!in_array($value, $availableFields)) unset($fieldsQuery[$key]);
			else if(!isset($index)) $index = $value;			
		
		// DIVIDE BY
		if(isset($manualQuery['DIVIDE']))
		{
			$divideBy = $manualQuery['DIVIDE'];
			unset($manualQuery['DIVIDE']);
		}		
		
		// Valeurs par défaut
		if(!isset($manualQuery['SELECT'])) $manualQuery['SELECT'] = implode(',', $fieldsQuery);
		if(!isset($manualQuery['FROM'])) $manualQuery['FROM'] = $this->table;
		if(!isset($manualQuery['ORDER BY'])) $manualQuery['ORDER BY'] = $index. ' DESC';
		
		// WHERE
		$fields = db::fields($this->table);
		if(MULTILANGUE and $this->multilangue and in_array('langue', $fields))
		{
			$whereMulti = 'langue="' .$_SESSION['admin']['langue']. '"';
			$manualQuery['WHERE'] = (!isset($manualQuery['WHERE']))
				? $whereMulti
				: $whereMulti. ' AND ' .$manualQuery['WHERE'];
		}	
										
		// Tri des arguments
		$ordreSyntaxe = array('SELECT', 'FROM', 'LEFT JOIN', 'WHERE', 'GROUP BY', 'ORDER BY', 'LIMIT');
		foreach($ordreSyntaxe as $argument)
		{
			if(array_key_exists($argument, $manualQuery))
			{
				$orderedQuery[$argument] = $manualQuery[$argument];
				unset($manualQuery[$argument]);
			}
		}

		$query = simplode(' ', ' ', $orderedQuery);
		$items = a::rearrange(db::query($query));
		if($items)
		{
			foreach($items as $key => $value)
			{
				// Divisions
				if(!empty($divideBy))
				{
					if(!isset($thisGroup) or $thisGroup != $value[$divideBy])
					{
						echo '<tr class="entete"><td colspan="50" class="opener" opener="' .str::slugify($value[$divideBy]). '">' .ucfirst($value[$divideBy]). '</td></tr>';
						$thisGroup = $value[$divideBy];
					}
				}
				
				$selected = ($this->getEdit	== $key) ? 'selected' : NULL;
				if(isset($thisGroup)) echo '<tr id="' .$key. '" class="opened ' .$selected. '" opened="' .str::slugify($thisGroup). '">';
				else echo '<tr id="' .$key. '" class="' .$selected. '">';
				
					// Valeurs
					$value = a::beArray($value);
					foreach($fieldsDisplay as $fieldName)
						echo '<td>' .stripslashes(str_replace('<br />', ' ', $value[$fieldName])). '</td>'; 
					
					// Gestion
					if(isset($this->tableRows))
						foreach($this->tableRows as $function => $name)
						{
							echo 
							'<td>'
								.str::slink(
									'admin-' .$this->table,
									str::img(
										'assets/css/' .$function. '.png',
										$name),
									array($function. '_' .$this->table => $key),
									array('title' => $name)).
							'</td>';
						}
				echo '</tr>';
			}
		}
		else echo '<tr><td colspan="50">' .l::get('admin.no_results', 'Aucun élément à afficher'). '</td></tr>';
		
		// Ajouter un élément
		echo '
		<tr class="additem"><td colspan="50">'
			.str::slink(NULL, l::get('admin.add', 'Ajouter un élément'), 'add_'.$this->table).'
		</td></tr>
		</tbody></table><br /><br />';
	}
				
	/*
	########################################
	######## FONCTIONS FORMULAIRES #########
	########################################
	*/
		
	// Détermine si le formulaire est en mode ajout ou modif
	function addOrEdit(&$typeEdit = NULL, &$editText = NULL, &$urlAction = NULL)
	{
		if($this->getEdit)
		{
			$typeEdit = $this->getEdit;
			$editText = 'Modifier';
			$urlAction = 'edit_' .$this->table. '=' .$this->getEdit;
		}
		else
		{
			$typeEdit = 'add';
			$editText = 'Ajouter';
			$urlAction = 'add';
		}	
	}
	function formAddOrEdit($formulaire = NULL)
	{
		if(!empty($formulaire))
			if(isset($this->getEdit) or isset($this->getAdd)) return $formulaire;
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
	
	/*
	Mode de sauvegarde de l'image
	# TABLE - présence d'une tableur sœur TABLE_thumbs contenant les images.
		Plusieurs image
	# PATH - Champ path dans la table même stockant l'url de l'image
		Une image
	# ID - L'image prend l'id de l'entrée pour reconnaissance
		Une image
	*/
	function imageMode($table = NULL)
	{
		if(!$table) $table = $this->table;
		
		if(config::get('image.' .$table)) return config::get('image.' .$table);
		else
		{
			if(db::is_table($table. '_thumb')) $storageMode = 'table';
			elseif(in_array('path', db::fields($table))) $storageMode = 'path';
			else $storageMode = 'id';
			
			config::set('image.' .$table, $storageMode);
			return $storageMode;
		}
	}
	function getImage($idpic)
	{
		$mode = $this->imageMode();
		switch($mode)
		{
			case 'table':
				$image = db::field($this->table, 'path', array('id_' .$this->table => $idpic));
				break;
				
			case 'path':
				$image = db::field($this->table, 'path', array('id' => $idpic));
				break;
				
			case 'default':
				$image = basename(a::simple(glob('assets/file/' .$this->table. '/' .$idpic. '.*')));
				break;
		}
		return $image;
	}
	
	// Envoyer une image
	function uploadImage($field = 'thumb')
	{
		$GLOBALS['cerberus']->injectModule('filecat');
		
		if(isset($_FILES[$field]['name']) and !empty($_FILES[$field]['name']))
		{
			// Erreurs basiques
			$errorDisplay = NULL;
			$extension = f::extension($_FILES[$field]['name']);
			if($_FILES[$field]['error'] != 0) $errorDisplay .= 'Une erreur est survenue lors du transfert.';
			if(filecat($extension) != 'image') $errorDisplay .= '<br />L\'extension du fichier n\'est pas valide';
					
			// Si aucune erreur
			if(empty($errorDisplay))
			{	
				$lastID = ($_POST['edit'] == 'add')
					? db::increment($this->table)
					: $_POST['edit'];
			
				$storageMode = $this->imageMode();
				switch($storageMode)
				{
					case 'table':
						$file = explode('.', $_FILES[$field]['name']);
						$file = str::slugify($file[0]). '-' .md5(str::random()). '.' .$extension;
						db::insert($this->table. '_thumb', array('path' => $file, 'id_' .$this->table => $lastID));
						break;
						
					case 'path':
						$path = db::field($this->table, 'path', array($this->index => $lastID));
						$file = $lastID. '-' .md5(str::random()). '.' .$extension;
						
						if(isset($path) and !empty($path)) sunlink('assets/file/' .$this->table. '/' .$path);
						db::update($this->table, array('path' => $file), array('id' => $lastID));
						break;
						
					default:
						$file = $lastID. '.' .$extension;
						break;
				}
				
				// Sauvegarde de l'image
				$resultat = move_uploaded_file($_FILES[$field]['tmp_name'], 'assets/file/' .$this->table. '/' .$file);
				if($resultat) prompt('Image ajoutée au serveur');
				else prompt('Une erreur est survenue lors du transfert.');
			}
			else prompt($errorDisplay);
		}
	}
}
?>