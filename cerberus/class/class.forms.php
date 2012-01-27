<?php
class forms
{
	// Etats actifs
	private $tabs = 0;
	private $infieldset = FALSE;
	private $values;
	
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
	
	function validate()
	{
		$validate = r::parse(func_get_args());
		
		echo r::method();
		a::show(r::data());
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
		
		// PARAMÈTRES DU CHAMP
		$deploy['type'] = a::get($params, 'type', 'text');
		$label = a::get($params, 'label', $deploy['type']); // Si aucun label, on utilise le nom slugifié
		$status = a::get($params, 'status');
		$deploy['name'] = a::get($params, 'name', str::slugify($label));
		$deploy['name'] = l::get($deploy['name'], $deploy['name']);
		$deploy['value'] = a::get($_POST, $deploy['name'], a::get($this->values, $deploy['name']));
		$deploy['class'] = a::get($params, 'class');
		$deploy['placeholder'] = a::get($params, 'placeholder');
		if(is_array($deploy['class'])) $deploy['class'] = implode(' ', $deploy['class']);
		
		// Paramètres auxiliaires
		$mandatory = a::get($params, 'mandatory');
		
		// Classe du champ
		$classes = $deploy['type'] == 'submit' ? array('form-actions') : array('control-group');
		$classes[] = $deploy['type'];
		if($mandatory) $classes[] = 'mandatory';
		if($status) $classes[] = $status;
		
		////////////////////
		/////// RENDU //////
		////////////////////
		
		if($this->optionFormType == 'horizontal')
			$this->rend('<div class="' .implode(' ', $classes). '">', 'TAB');
		
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
					$this->rend('<button ' .$this->paramRender($deploy). '>' .$deploy['name']. '</button>');
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
	
	function addText($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addElement(array('label' => $label, 'name' => $name, 'type' => 'text', 'value' => $value, 'params' => $additionalParams));
	}
	function addCheckbox($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addElement(array('label' => $label, 'name' => $name, 'type' => 'checkbox', 'value' => $value, 'params' => $additionalParams));
	}
	function addCheckboxes()
	{
		// TO DEFINE
	}
	function addFile($name, $label = NULL, $additionalParams = NULL)
	{
		$this->render = str_replace('method="' ,'enctype="multipart/form-data" method="', $this->render);
		$this->addElement(array('label' => $label, 'name' => $name, 'type' => 'file', 'params' => $additionalParams));
	}
	function addSubmit($name = 'Valider', $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		if(!$additionalParams) $additionalParams['class'] = 'primary';
		$this->addElement(array('label' => $label, 'name' => $name, 'type' => 'submit', 'value' => $value, 'params' => $additionalParams));
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