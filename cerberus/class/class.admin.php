<?php
class admin extends admin_setup
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
		$this->usable = str_replace('cerberus_', NULL, $table);
		$this->getEdit = get('edit_' .$this->usable, NULL);
		$this->getAdd = get('add_' .$this->usable, NULL);

		// Champs de mise à jour
		$fieldsUpdate = array();
		$facultativeFields = a::force_array($facultativeFields);
		
		// Récupération du nom des champs
		$this->fields = db::fields($table);
		$this->types = a::rearrange(db::query('EXPLAIN ' .$this->table, 'Field', true));
		$this->index = a::get($this->fields, 0, 'id');
		
		// AJOUT ET MODIFICATION
		if(isset($_POST['edit'])) 
		{
			// Vérification des champs disponibles
			$emptyFields = array();
			if(MULTILANGUE and in_array('langue', db::fields($this->table))) $fieldsUpdate['langue'] = l::admin_current();
			foreach($_POST as $key => $value)
			{
				if(str::find('_annee', $key))
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
					str::display('Objet ajouté', 'success');
				}
				else
				{
					db::update($this->table, $fieldsUpdate, array($this->index => $_POST['edit']));
					str::display('Objet modifié', 'success');
				}
				$uploadImage = $this->uploadImage();
			}
			else str::display('Un ou plusieurs champs sont incomplets : ' .implode(', ', $emptyFields), 'error');
		}
		
		// SUPPRESSION
		if(isset($_GET['delete_' .$this->usable]))
		{
			// Images liées
			if(in_array('path', $this->fields))
			{
				$path = db::field($this->table, 'path', array($this->index => $_GET['delete_' .$this->usable]));
				if(isset($path) and !empty($path)) f::remove(PATH_FILE.$this->usable. '/' .$path);
			}
			else
			{
				if(file_exists(PATH_FILE.$this->usable))
				{
					$picExtension = array('jpg', 'jpeg', 'gif', 'png');
					foreach($picExtension as $value)
					{
						$thisFile = $_GET['delete_' .$this->usable]. '.' .$value;
						f::remove(PATH_FILE.$this->usable. '/' .$thisFile);
					}
				}
			}
						
			db::delete($this->table, array($this->index => $_GET['delete_' .$this->usable]));
			str::display('Objet supprimé');
		}	
		if(isset($_GET['deleteThumb']))
		{
			$image = $this->getImage($_GET['deleteThumb']);
			
			if(f::remove($image)) str::display('Miniature supprimée', 'success');
			else str::display('Miniature introuvable', 'error');
			
			if(isset($lastID)) f::remove(glob(PATH_FILE.$this->usable. '/' .$lastID. '-*.*'));
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
		echo '<table class="table table-striped table-bordered table-condensed"><thead><tr>';
		
		/* ######## EN-TÊTE ########## */
		
		// Colonnes principales
		a::force_array($fieldsDisplay);
		foreach($fieldsDisplay as $key => $value)
		{
			$nomColonne = (is_numeric($key))
				? $value
				: $key;
			echo '<th class="tablerow-data">' .ucfirst($nomColonne). '</th>';
		}
		
		// Colonnes gestion
		if(isset($this->tableRows))
			foreach($this->tableRows as $function => $name)
				echo '<th class="tablerow-function">' .$name. '</th>';
				
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
			$whereMulti = 'langue="' .l::admin_current(). '"';
			$manualQuery['WHERE'] = (!isset($manualQuery['WHERE']))
				? $whereMulti
				: $whereMulti. ' AND ' .$manualQuery['WHERE'];
		}	
										
		// Tri des arguments
		$ordreSyntaxe = array('SELECT', 'FROM', 'RIGHT JOIN', 'LEFT JOIN', 'WHERE', 'GROUP BY', 'ORDER BY', 'LIMIT');
		foreach($ordreSyntaxe as $argument)
		{
			if(array_key_exists($argument, $manualQuery))
			{
				$orderedQuery[$argument] = $manualQuery[$argument];
				unset($manualQuery[$argument]);
			}
		}

		$query = a::glue($orderedQuery, ' ', ' ');
		$items = a::rearrange(db::query($query));
		
		/* ######## AFFICHAGE DU TABLEAU ########## */
		
		if($items)
		{
			// Fonctions en cours d'utilisation
			$SELECTED = NULL;
			if(isset($this->tableRows))
				foreach($this->tableRows as $function => $name)
					if(isset($_GET[$function. '_' .$this->usable])) $SELECTED = $_GET[$function. '_' .$this->usable];
			
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
				
				$selected = ($SELECTED == $key) ? 'selected' : NULL;
				if(isset($thisGroup)) echo '<tr id="' .$key. '" class="opened ' .$selected. '" opened="' .str::slugify($thisGroup). '">';
				else echo '<tr id="' .$key. '" class="' .$selected. '">';
				
					// Valeurs
					$value = a::force_array($value);
					foreach($fieldsDisplay as $fieldName)
					{
						$fieldIndex = a::get(explode('.', $fieldName), 1, $fieldName);
						$fieldValue = stripslashes(str_replace('<br />', ' ', $value[$fieldIndex]));
						if(isset($this->types[$fieldIndex]) and $this->types[$fieldIndex]['Type'] == "enum('0','1')") $fieldValue = ($fieldValue == 0) ? 'Non' : 'Oui'; // Booléen
						echo '<td class="tablerow-data">' .$fieldValue. '</td>'; 
					}
					
					// Gestion
					if(isset($this->tableRows))
						foreach($this->tableRows as $function => $name)
						{
							// Colonne personnalisée
							if(str::find('{key}', $function))
								echo '<td>' .str_replace('{key}', $key, $function). '</td>';
							
							// Fonctions
							else echo 
							'<td>'
								.str::slink(
									'admin-' .$this->usable,
									str::img(
										PATH_CERBERUS.'img/action-' .$function. '.png',
										$name),
									array($function. '_' .$this->usable => $key),
									array('title' => $name)).
							'</td>';
						}
				echo '</tr>';
			}
		}
		else echo '<tr><td colspan="50">' .l::get('admin.no_results'). '</td></tr>';
		
		// Ajouter un élément
		echo '
		<tr class="additem"><td colspan="50">'
			.str::slink(NULL, l::get('admin.add'), 'add_'.$this->usable, array('class' => 'btn btn-wide btn-cerberus')).'
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
			$urlAction = 'edit_' .$this->usable. '=' .$this->getEdit;
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
				$image = PATH_FILE.$this->usable. '/' .db::field($this->table, 'path', array('id_' .$this->table => $idpic));
				break;
				
			case 'path':
				$image = PATH_FILE.$this->usable. '/' .db::field($this->table, 'path', array($this->index => $idpic));
				break;
				
			case 'default':
				$image = a::simplify(glob(PATH_FILE.$this->usable. '/' .$idpic. '.*'));
				break;
		}
		return $image;
	}
	
	// Envoyer une image
	function uploadImage($field = 'thumb')
	{
		if(isset($_FILES[$field]['name']) and !empty($_FILES[$field]['name']))
		{
			// Erreurs basiques
			$errorDisplay = NULL;
			$extension = f::extension($_FILES[$field]['name']);
			if($_FILES[$field]['error'] != 0) $errorDisplay .= 'Une erreur est survenue lors du transfert.';
			if(f::filecat($extension) != 'image') $errorDisplay .= '<br />L\'extension du fichier n\'est pas valide';
					
			// Si aucune erreur
			if(empty($errorDisplay))
			{	
				$lastID = ($_POST['edit'] == 'add')
					? db::increment($this->table) - 1
					: a::get($_POST, 'edit');
						
				$storageMode = $this->imageMode();
				switch($storageMode)
				{
					case 'table':
						$file = explode('.', $_FILES[$field]['name']);
						$file = str::slugify($file[0]). '-' .md5(str::random()). '.' .$extension;
						db::insert($this->table. '_thumb', array('path' => $file, 'id_' .$this->table => $lastID));
						break;
						
					case 'path':
						$file = $lastID. '-' .str::slugify($_FILES[$field]['name']). '-' .md5(str::random()). '.' .$extension;

						$path = db::field($this->table, 'path', array($this->index => $lastID));
						if($path) f::remove($path);
						f::remove(glob(PATH_FILE.$this->usable. '/' .$lastID. '-*.*'));
						
						db::update($this->table, array('path' => $file), array('id' => $lastID));
						break;
						
					default:
						$file = $lastID. '.' .$extension;
						break;
				}
				
				// Sauvegarde de l'image
				
				$resultat = move_uploaded_file($_FILES[$field]['tmp_name'], PATH_FILE.$this->usable. '/' .$file);
				if($resultat) str::display(l::get('admin.upload.success'), 'success');
				else str::display(l::get('admin.upload.error'), 'error');
			}
			else str::display($errorDisplay, 'error');
		}
	}
}
?>