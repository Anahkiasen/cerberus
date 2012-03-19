<?php
class forms
{
	// Etats actifs
	private $tabs;
	private $infieldset = FALSE;
	private $values;
	private $status = array();
	
	// Options
	private $optionMultilangue;
	private $optionFormType;
	
	// Rendu
	private $render;
	
	/*
	########################################
	###### METHODES DE CONSTRUCTION ########
	######################################## 
	*/
	
	function __construct($params = NULL, $multilangue = NULL)
	{
		// Création de l'élément <form>
		if(!isset($params['method'])) $params['method'] = 'post';
		if(!isset($params['class'])) $params['class'] = 'form-horizontal';
		
		$this->tabs = a::get($params, 'tabs', 1);
		$this->optionMultilangue = $multilangue ? $multilangue : MULTILANGUE;
		$this->rend('<form ' .$this->paramRender($params, 'tabs'). '>');
		
		// Type de formulaire
		$formClass = a::get($params, 'class');
		if(str::find('form-horizontal', $formClass)) $this->optionFormType = 'horizontal';
		elseif(str::find('form-search', $formClass)) $this->optionFormType = 'search';
		elseif(str::find('form-inline', $formClass)) $this->optionFormType = 'inline';
		else $this->optionFormType = 'horizontal';	
	}
	
	// Si le formulaire est envoyé, on analyse les champs et on les retourne nettoyés
	function validate()
	{
		if(!isset($_POST) or empty($_POST)) return FALSE;
		
		$parser = func_get_args();
		$mailbody = NULL;
		$result = array();
		$errors = array();
		
		// Analyse des résultats du formulaire
		foreach($parser as $field)
		{
			$params			= explode(':', $field);
			$key      		= a::get($params, 0);
			$type				= a::get($params, 1, $key);
			$default			= a::get($params, 2, NULL);
			$value 			= $type == 'file'
									? a::get($_FILES, $key)
									: str::sanitize(r::get($key, $default), $type);
								
			$status 					= (v::check($value, $type) and a::get($value, 'error', 0) == 0);
			$this->status[$key] 	= $status ? 'success' : 'error';
			
			$result[$key] 				= $value;
			if(!$status) $errors[] 	= $key;
		}
		
		// Affichage des erreurs
		if(!empty($errors))
		{
			return array(
				'msg' => l::get('form.incomplete'),
				'result' => $result,
				'status' => FALSE);
		}
		else
		{
			foreach($result as $key => $value)
			{
				$mailbody .= '<strong>' .l::get('form-' .$key, ucfirst($key)). '</strong> : ';
				if(is_array($value)) 
				{
					$mailbody .= '<br />';
					$value = a::simplode(' : ', '<br />', $value);
				}
				$mailbody .= stripslashes($value). '<br />';
			}
			
			return array(
				'status' => TRUE,
				'result' => $result,
				'mail' => $mailbody);
		}	
	}
	
	/*
	########################################
	######## FIELDSETS ET FONCTIONS ########
	######################################## 
	*/
	
	// Ouvrir et fermer un fieldset
	function openFieldset($name)
	{
		$this->rend('<fieldset class="' .str::slugify($name). '">', 'TAB');
		
		$this->infieldset = TRUE;
		$fieldset_name = l::get('form-'.$name, ucfirst($name));
		
		$this->rend('<legend>' .$fieldset_name. '</legend>');
	}
	
	function closeFieldset()
	{
		$this->rend('</fieldset>', 'UNTAB');
	}
	
	// Modifier les valeurs du tableau
	function values($table)
	{
		$this->usable = str_replace('cerberus_', NULL, $table);
		$id = db::fields($table);
		$id = in_array('id', $id) ? 'id' : a::get($id, 0);
		
		if(isset($_GET['edit_'.$this->usable]))
			$this->values = db::row($table, '*', array($id => $_GET['edit_'.$this->usable]));
	}
	function setValues($array)
	{
		foreach($array as $key => $val) $this->values[$key] = $val;
	}
	
	/*
	########################################
	############### CHAMPS  ################
	######################################## 
	*/
	
	function addElement($params)
	{
		// Fusion des arrays entre eux
		if(is_array($params))
		{
			$params = array_merge($params, a::get($params, 'params', array()));
			$params = a::remove($params, 'params');
		}
		else $params = str::parse('{'.$params.'}', 'json');
		
		/////////////////////////
		// PARAMÈTRES DU CHAMP //
		/////////////////////////
		
		// Type du champ
		$deploy['type'] = a::get($params, 'type', 'text');
							
		// Label du champ
		$label = 	a::get($params, 'label',
						l::get('form-' .a::get($params, 'name'),
						ucfirst(a::get($params, 'name'))),					
						$deploy['type']);
		
		// Attribut name
		$deploy['name'] = 	a::get($params, 'name', 
							str::slugify($label));
		$deploy['name'] = 	l::get($deploy['name'], 
							$deploy['name']);
		
		// Valeur du champ
		$isset_post = isset($_POST) ? $_POST : NULL; 
		$deploy['value'] = 	a::get($isset_post, $deploy['name'], 
									a::get($params, 'value',
									a::get($this->values, $deploy['name'])));
							
		// Paramètres auxiliaires et data-*
		$auxiliaires = array('placeholder', 'style', 'rel', 'rows', 'id', 'disabled', 'select');
		foreach($params as $key => $value)
			if(in_array($key, $auxiliaires) or str::find('data-', $key)) $deploy[$key] = $value;
			if(a::get($deploy, 'data-provide') == 'typeahead') $deploy['autocomplete'] = 'off';
		
		// Champ obligatoire
		$mandatory = a::get($params, 'mandatory');
		if(isset($params['multiple'])) $deploy['multiple'] = 'multiple';
		
		// Listes
		$checkboxes = a::get($params, 'checkboxes');
		
		// Add-ons
		$prepend = a::get($params, 'prepend');
		$append = a::get($params, 'append');
		$prepend_type = $prepend ? 'prepend' : 'append';
		$addon = a::get($params, 'addon');
		
		// Classe du champ
		$deploy['class'] = a::get($params, 'class');
		if(is_array($deploy['class'])) $deploy['class'] = implode(' ', $deploy['class']); 
		if($addon) $deploy['class'] .= ' ' .$addon;
		
		$div_class = $deploy['type'] == 'submit' ? array('form-actions') : array('control-group');
		$div_class[] = a::get($params, 'status', a::get($this->status, $deploy['name']));
		$div_class[] = str::slugify($label);
		$div_class[] = str::slugify($deploy['type']);
		if($mandatory) $div_class[] = 'mandatory';
		
		////////////////////
		/////// RENDU //////
		////////////////////
		
		$openDiv = ($this->optionFormType == 'horizontal' and $deploy['type'] != 'hidden');
		
		if($openDiv)
			$this->rend('<div class="' .implode(' ', $div_class). '">', 'TAB');
		
			// LABEL
			if(!in_array($deploy['type'], array('submit', 'checkbox', 'hidden')) and $this->optionFormType != 'search')
				$this->rend('<label for="' .$deploy['name']. '" class="control-label">' .$label. '</label>');
			
			// DIV ENGLOBANTE
			$englobe = ($deploy['type'] != 'submit' and $this->optionFormType == 'horizontal');
			if($englobe) $this->rend('<div class="controls">', 'TAB');
			if($prepend or $append) $this->rend('<div class="input-' .$prepend_type. '">', 'TAB');
			
			// CHAMP MÊME
			if($prepend) $this->rend('<span class="add-on">' .$prepend. '</span>', 'TAB');
			switch($deploy['type'])
			{
				// Texte
				case 'text':
				case 'hidden':
				case 'password':
					if($addon == 'uneditable-input') $this->rend('<span ' .$this->paramRender($deploy, 'value'). '>' .$deploy['value']. '</span>');
					elseif($addon == 'disabled') $this->rend('<input ' .$this->paramRender($deploy, 'value'). ' placeholder="' .$deploy['value']. '" disabled />');
					else $this->rend('<input ' .$this->paramRender($deploy). ' />');
					break;
					
				case 'textarea':
					$this->rend('<textarea ' .$this->paramRender($deploy, 'value'). '>' .$deploy['value']. '</textarea>');
					break;
					
				case 'checkbox':
					$this->rend('<label class="checkbox">');
					$this->rend('<input ' .$this->paramRender($deploy). ' /> '.$label);
					$this->rend('</label>');
					break;
				
				// Listes
				case 'checkboxes':
					$nameCheckbox = a::get($params, 'name').'[]';
					$postCheckbox = a::get($_POST, $deploy['name'], array());
					if($checkboxes) foreach($checkboxes as $check_index => $check_label)
					{
						$checked = in_array($check_index, $postCheckbox) ? ' checked="checked"' : NULL;	
						$this->rend('<label class="checkbox ' .$deploy['class']. '">');
						$this->rend('<input type="checkbox" name="' .$nameCheckbox. '" value="' .$check_index. '" ' .$checked. ' /> '.$check_label);
						$this->rend('</label>');
					}
					break;
										
				case 'radio':
					foreach($deploy['value'] as $radio_index => $radio_label)
					{
						$checked = r::get($deploy['name']) == $radio_index ? ' checked="checked"' : NULL;	
						$this->rend('<label class="radio ' .$deploy['class']. '">');
						$this->rend('<input ' .$this->paramRender($deploy, 'value').$checked. ' value="' .$radio_index. '" /> '.$radio_label);
						$this->rend('</label>');
					}
					break;
				
				case 'select':
					if(isset($deploy['multiple'])) $deploy['name'] .= '[]';
					if(!is_array(current($deploy['select']))) $deploy['select'] = array($deploy['select']);
					
					foreach($deploy['select'] as $array_label => $array_entries)
					{
						$array_label = sizeof($deploy['select']) > 1 ? $deploy['name']. '_' .$array_label : $deploy['name'];
						$array_value = a::get($deploy, 'value', a::get($this->values, $array_label, r::get($array_label)));
						
						$this->rend('<select name="' .$array_label. '" ' .$this->paramRender($deploy, array('value', 'select', 'name')). '>');
						foreach($array_entries as $index => $label)
						{
							if(is_numeric($index) and !isset($params['force_index'])) $index = $label;
							
							$selected = $array_value == $index ? ' selected="selected"' : NULL;	
							$this_option = $index == $label ? NULL : ' value="' .$index. '"';
							$this->rend('<option' .$this_option.$selected. '>'.$label. '</option>');
						}
						$this->rend('</select>');
					}
					break;
								
				// Fonctions
				case 'file':
					$this->rend('<input ' .$this->paramRender($deploy). ' />');
					break;
					
				case 'submit':
					$deploy['class'] .= ' btn';
					$this->rend('<button ' .$this->paramRender($deploy, array('label', 'name')). ' data-loading-text="Chargement">' .$label. '</button>');
					
					// Bouton annuler
					if($this->optionFormType != 'inline' and a::get($params, 'cancel', FALSE))
						$this->rend('<button type="reset" class="btn">' .l::get('form.cancel', 'Annuler'). '</button>');
					break;
			}

			// APPEND
			if($append) $this->render .= str::find('button:', $append)
				? '<p class="btn btn-danger add-on">' .substr($append, 7). '</p>'
				: '<span class="add-on">' .$append. '</span>';
			if($prepend or $append) $this->rend('</div>', 'UNTAB');
			
			// AIDE CONTEXTUELLE
			if(a::get($params, 'help')) $this->rend('<p class="help-block">' .a::get($params, 'help'). '</p>');
			if(a::get($params, 'help-inline')) $this->rend('<p class="help-inline">' .a::get($params, 'help-inline'). '</p>');
			
			if($englobe) $this->rend('</div>', 'UNTAB');
					
		if($openDiv)
			$this->rend('</div>', 'UNTAB');
	}
	
	/*
	########################################
	############## RACCOURCIS ##############
	######################################## 
	*/
	
	// Add a field to the main form
	function addField($name, $label, $type, $value, $additionalParams)
	{
		if(!is_array($additionalParams)) $additionalParams = str::parse('{' .$additionalParams. '}', 'json');
		$this->addElement(array('label' => $label, 'name' => $name, 'value' => $value, 'type' => $type, 'params' => $additionalParams));
	}
	
	//////////////////
	// CHAMPS TEXTE //
	//////////////////
	
	function addText($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addField($name, $label, 'text', $value, $additionalParams);
	}
	
	function addPassword($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addField($name, $label, 'password', $value, $additionalParams);
	}
	
	function addTextarea($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addField($name, $label, 'textarea', $value, $additionalParams);
	}

	function addCheckbox($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addField($name, $label, 'checkbox', $value, $additionalParams);
	}
	
	function addHidden($name, $value = NULL, $additionalParams = NULL)
	{
		$this->addField($name, $label = NULL, 'hidden', $value, $additionalParams);
	}
	
	//////////////////
	///// LISTES /////
	//////////////////
	
	function addCheckboxes($name = NULL, $label = NULL, $checkboxes, $additionalParams = NULL)
	{
		$additionalParams['checkboxes'] = $checkboxes;
		$this->addField($name, $label, 'checkboxes', NULL, $additionalParams);
	}
	function addRadio($name = NULL, $label = NULL, $radio, $additionalParams = NULL)
	{
		$this->addField($name, $label, 'radio', $radio, $additionalParams);
	}
	function addSelect($name = NULL, $label = NULL, $select, $value = NULL, $additionalParams = NULL)
	{
		$additionalParams['select'] = $select;
		$this->addField($name, $label, 'select', $value, $additionalParams);
	}
	function addDate($name = 'date', $label = NULL, $value = '0000-00-00', $additionalParams = NULL)
	{
		$value = explode('-', $value);
		$additionalParams['class'] = 'dateForm';
		$startingYear = a::get($additionalParams, 'start');
		$endingYear = a::get($additionalParams, 'end');
		
		$this->setValues(array($name.'_jour' => $value[2], $name.'_mois' => $value[1], $name.'_annee' => $value[0]));
		$this->addSelect($name, $label, $this->liste_date($startingYear, $endingYear), NULL, $additionalParams);
	}
	
	//////////////////
	/// FUNCTIONS ////
	//////////////////

	function addType()
	{
		$formType = a::get($_GET, 'edit_' .$this->usable, 'add');
		$this->addHidden('edit', $formType);
	}
	function addFile($name, $label = NULL, $additionalParams = NULL)
	{
		$this->render = str_replace('method="' ,'enctype="multipart/form-data" method="', $this->render);
		$this->addField($name, $label, 'file', NULL, $additionalParams);
	}	
	function addSubmit($name = 'Valider', $additionalParams = NULL)
	{
		if(!$additionalParams) $additionalParams['class'] = 'btn-primary';
		$this->addField($name, NULL, 'submit', NULL, $additionalParams);
	}
	
	//////////////////
	//// SELECTS /////
	//////////////////
	
	function liste_number($end, $start = 0, $step = 1)
	{
		return range($start, $end, $step);
	}
	
	// Champ date
	function liste_date($startingYear = NULL, $endingYear = NULL)
	{
		if(!$startingYear) $startingYear = date('Y');
		if(!$endingYear) $endingYear = $startingYear + 10;
		
		return array(
			'jour' => $this->liste_number(31, 1),
			'mois' => $this->liste_number(12, 1),
			'annee' => $this->liste_number($endingYear, $startingYear));
	}
	
	/*
	########################################
	######## RENDU DU FORMULAIRE ###########
	######################################## 
	*/
	
	function insert($text)
	{
		$this->render .= $text;
	}
	
	function rend($content, $tabs = NULL)
	{
		if($tabs == 'UNTAB') $this->tabs--;
		if($this->tabs >= 0) $this->render .= str_repeat("\t", $this->tabs).$content.PHP_EOL;
		if($tabs == 'TAB') $this->tabs++;
	}
	
	function paramRender($params, $except = NULL)
	{
		$render = NULL;
		if(!is_array($except)) $except = array($except);
		
		foreach($params as $key => $value)
			if(!empty($value) and !in_array($key, $except)) $render .= $key.'="' .$value. '" ';

		return substr($render, 0, -1);
	}
	
	function render()
	{
		echo $this->returns();
	}
	
	function returns()
	{
		// Rendu du formulaire
		$this->rend('</form>', 'UNTAB');
		return $this->render;
	}
}
?>