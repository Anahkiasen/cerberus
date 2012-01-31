<?php
class forms
{
	// Etats actifs
	private $tabs = 0;
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
		$this->tabs = a::get($params, 'tabs');
		$this->optionMultilangue = $multilangue ? $multilangue : MULTILANGUE;
		$this->rend('<form ' .$this->paramRender($params). '>', 'TAB');
		
		// Type de formulaire
		if(str::find('form-horizontal', $params['class'])) $this->optionFormType = 'horizontal';
		elseif(str::find('form-search', $params['class'])) $this->optionFormType = 'inline';
		elseif(str::find('form-inline', $params['class'])) $this->optionFormType = 'inline';
		else $this->optionFormType = 'vertical';		
	}
	
	// Si le formulaire est envoyé, on analyse les champs et on les retourne nettoyés
	function validate()
	{
		$parser = func_get_args();
		if(isset($_POST) and !empty($_POST))
		{
			if($parser)
			{
				$validate = r::parse($parser);
				foreach($validate as $fieldname => $fieldvalue)
					$this->status[$fieldname] =
						(empty($fieldvalue) or (is_array($fieldvalue) and $fieldvalue['error'] != 0)) ? 'error' : 'success';	
				return $validate;
			}
			else return $_POST;
		}
		else return NULL;
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
	
	function values($table)
	{
		if(isset($_GET['edit_'.$table]))
		{
			$this->values = db::row($table, '*', array('id' => $_GET['edit_'.$table]));
		}
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
		$deploy['type'] = 	a::get($params, 'type', 
							'text');
							
		// Label du champ
		$label = 	a::get($params, 'label', 
					ucfirst(a::get($params, 'name', 
					$deploy['type'])));
		
		// Attribut name
		$deploy['name'] = 	a::get($params, 'name', 
							str::slugify($label));
		$deploy['name'] = 	l::get($deploy['name'], 
							$deploy['name']);
		
		// Valeur du champ
		$deploy['value'] = 	a::get($params, 'value',
							a::get($_POST, $deploy['name'], 
							a::get($this->values, $deploy['name'])));
		
		// Paramètres auxiliaires
		$deploy['placeholder'] = a::get($params, 'placeholder');
		$mandatory = a::get($params, 'mandatory');
		
		// Classe du champ
		$deploy['class'] = a::get($params, 'class');
		if(is_array($deploy['class'])) $deploy['class'] = implode(' ', $deploy['class']);
		
		$div_class = $deploy['type'] == 'submit' ? array('form-actions') : array('control-group');
		$div_class[] = $deploy['type'];
		$div_class[] = a::get($this->status, $deploy['name'], a::get($params, 'status'));
		if($mandatory) $div_class[] = 'mandatory';
		
		////////////////////
		/////// RENDU //////
		////////////////////
		
		if($this->optionFormType == 'horizontal')
			$this->rend('<div class="' .implode(' ', $div_class). '">', 'TAB');
		
			// LABEL
			if(!in_array($deploy['type'], array('submit', 'checkbox', 'hidden')) and $this->optionFormType != 'inline')
				$this->rend('<label for="' .$deploy['name']. '" class="control-label">' .$label. '</label>');
			
			// DIV ENGLOBANTE
			$englobe = ($deploy['type'] != 'submit' and $this->optionFormType == 'horizontal');
			if($englobe) $this->rend('<div class="controls">', 'TAB');
			
			// CHAMP MÊME
			switch($deploy['type'])
			{
				case 'text':
					$this->rend('<input ' .$this->paramRender($deploy). ' />');
					break;
					
				case 'checkbox':
					$this->rend('<label class="checkbox">');
					$this->rend('<input ' .$this->paramRender($deploy). ' /> '.$label);
					$this->rend('</label>');
					break;
					
				case 'file':
					$this->rend('<input ' .$this->paramRender($deploy). ' />');
					break;
					
				case 'submit':
					$deploy['class'] .= ' btn';
					$this->rend('<button ' .$this->paramRender($deploy). ' data-loading-text="Chargement">' .$deploy['name']. '</button>');
					if($this->optionFormType != 'inline') $this->rend('<button type="reset" class="btn">' .l::get('form.cancel', 'Annuler'). '</button>');
					break;
			}
			
			// AIDE CONTEXTUELLE
			if(a::get($params, 'help')) $this->rend('<p class="help-block">' .a::get($params, 'help'). '</p>');
			if(a::get($params, 'help-inline')) $this->rend('<p class="help-inline">' .a::get($params, 'help-inline'). '</p>');
	
			// /DIV ENGLOBANTE
			if($englobe) $this->rend('</div>', 'UNTAB');
					
		if($this->optionFormType == 'horizontal')
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
		$this->addElement(array('label' => $label, 'name' => $name, 'value' => $value, 'type' => $type, 'params' => $additionalParams));
	}
	
	//////////////////
	// CHAMPS TEXTE //
	//////////////////
	
	function addText($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addField($name, $label, 'text', $value, $additionalParams);
	}

	function addCheckbox($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addField($name, $abel, 'checkbox', $value, $additionalParams);
	}
	function addCheckboxes($name, $label = NULL, $checkboxes, $value, $additionalParams = NULL)
	{
		// Checkboxes (name => value)
		foreach($checkboxes as $check_name => $check_value)
		{
			if(in_array($check_name, $value)) $thisValue = 'ON';
			$this->addCheckbox($check_name, $check_value, $thisValue);
		}
	}
	
	//////////////////
	/// FUNCTIONS ////
	//////////////////

	function addFile($name, $label = NULL, $additionalParams = NULL)
	{
		$this->render = str_replace('method="' ,'enctype="multipart/form-data" method="', $this->render);
		$this->addField($name, $abel, 'file', $value, $additionalParams);
	}	
	function addSubmit($name = 'Valider', $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		if(!$additionalParams) $additionalParams['class'] = 'btn-primary';
		$this->addField($name, $label, 'submit', $value, $additionalParams);
	}
	
	/*
	########################################
	######## RENDU DU FORMULAIRE ###########
	######################################## 
	*/
	
	function rend($content, $tabs = NULL)
	{
		if($tabs == 'UNTAB') $this->tabs--;
		$this->render .= str_repeat("\t", $this->tabs).$content.PHP_EOL;
		if($tabs == 'TAB') $this->tabs++;
	}
	
	function paramRender($params)
	{
		$render = NULL;
		foreach($params as $key => $value)
			if(!empty($value)) $render .= $key.'="' .$value. '" ';
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