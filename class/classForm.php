<?php	
class form
{
	private $render;
	private $openedManual = false;
	
	private $multilangue = false;
	private $formType = 'ilec';
	
	/* ########################################
	############## FONCTIONS MOTEUR ##########
	######################################## */
	
	// Construction
	function __construct($method = 'post', $multilangue = true)
	{
		$this->multilangue = $multilangue;
		$this->render = '<form method="' .$method. '">';
	}
	function __toString()
	{
		$this->render .= '</form>';
		return $this->render;
	}
	function setFormType($type)
	{
		$this->formType = $type;
	}
	
	// Fieldsets
	function openFieldset($name)
	{
		$fieldName = ($this->multilangue == false) ? $name : index('form-' .$name);
		
		$this->render .= PHP_EOL. "
		<fieldset>" .PHP_EOL. "
			\t<legend>" .$fieldName. '</legend>';
		
		if($this->openedManual == true) $this->openedManual = false;
	}
	function closeFieldset()
	{
		if($this->openedManual == true) $this->closeManualField();
		$this->render .= PHP_EOL. '</fieldset>';
	}
	
	// Champs manuels
	function manualField($name, $full = false)
	{
		$fieldName = ($this->multilangue == false) ? $name : index('form-' .$name);
		
		if($this->formType != 'plain')
		{
				$this->render .= '<dl>';
				if($full == false)	$this->render .= '<dt><label for="' .$name. '">' .$fieldName. '</label></dt>';
				$this->render .= '<dd>';
		}
			
		$this->openedManual = true;
	}
	function closeManualField()
	{
		if($this->formType != 'plain') $this->render .= '</dd></dl>';
		$this->openedManual = false;
	}
	
	// Texte manuel
	function insertText($text)
	{
		$this->render .= $text;
	}
	
	/* #######################################
	######### CREATION DE L'ELEMENT ##########
	######################################## */
	
	/* fieldset
			dl
				dt label
				dd input */
	
	// Fonctions moteur
	function addElement($label, $name, $type, $value = '', $additionalParams = '')
	{
		$params = array("label" => $label, 'value' => $value, "type" => $type, "name" => $name);
		if(!empty($additionalParams) && is_array($additionalParams)) foreach($additionalParams as $key => $value) $params[$key] = $value;
			
		$this->attachElement($params);
	}
	function attachElement($params)
	{
		global $index;
				
		$type = $params['type'];

		// Nom du champ
		if(empty($params['label'])) $label = $params['name'];
		else $label = $params['label'];
		
		// Variable du champ
		$params['name'] = normalize(str_replace('-', '', $params['name']));
		
		// Valeur du champ
		if(isset($_POST[$params['name']]) && empty($params['value'])) $params['value'] = stripslashes($_POST[$params['name']]);
		if(isset($this->valuesArray[$params['name']]) && empty($params['value'])) $params['value'] = $this->valuesArray[$params['name']];
		
		// State Fieldset
		$stateField = ($this->openedManual == false 
		and $type != 'hidden' 
		and $this->formType != 'plain');
		
		// Ouverture du champ
		if($stateField)
		{
			$fieldName = ($this->multilangue == false) ? $label : index('form-' .$label);
	
			$this->render .= PHP_EOL. "
				\t<dl class=\"$type\">" .PHP_EOL;
			if($type != "submit") $this->render .= "\t\t<dt><label for=\"$label\">$fieldName</label></dt>" .PHP_EOL;
			$this->render .= "\t\t<dd>";

			// $this->render .= PHP_EOL. "\t<dd style=\"float: none; width: 100%\">";
		}
		
		// Suppression des paramètres inutiles		
		unset($params['label'], $params['type']);
		
		// LISTE DES CHAMPS
		if($type == 'text')
		{
			$this->render .= '<input type="text" ';
			foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
			$this->render .= ' />';
		}
		if($type == 'submit')
		{
			$fieldName = ($this->multilangue == false) ? $label : index('form-submit-' .$label);
			if($this->formType != 'plain') $this->render .= '<p style="text-align:center"><input type="submit" value="' .$fieldName. '" /></p>';
			else $this->render .= '<input type="submit" value="' .$fieldName. '" />';
		}
		if($type == 'textarea')
		{
			if(isset($params['bbcode'])) $this->render .= file_get_contents('include/scripts-bbcode.php');
			$this->render .= '<textarea id="textarea" ';
			foreach($params as $key => $value) if($key != 'value') $this->render .= $key. '="' .$value. '" ';
			$this->render .= '>' .$params['value']. '</textarea><p></p>';
		}
		if($type == 'hidden')
		{
			$this->render .= '<input type="hidden" ';
			foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
			$this->render .= ' />';
		}
		if($type == 'radio')
		{
			for($i = 0; $i <= $params['number']; $i++)
			{	
				$this->render .= '<input type="radio" ';
				foreach($params as $key => $value) if($key != 'value' && $key != 'number') $this->render .= $key. '="' .$value. '" ';
				$fieldName = ($this->multilangue == false) ? $label : index('form-' .$label. '-'.$i);
				if(isset($_POST[$label]) && $_POST[$label] == $i) $this->render .= 'checked="checked"';
				$this->render .= ' value="' .$i. '"> ' .$fieldName;
			}
		}
		if($type == 'file')
		{
			$this->render .= '<input type="file" ';
			foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
			$this->render .= ' />';
		}
		if($type == 'select')
		{
			$options = '';
			if(isset($params['select']))
			{
				foreach($params['select'] as $key => $value)
				{
					$options .= ($key == $params['value']) ? '<option value="' .$key. '" selected="selected">' : '<option value="' .$key. '">';
					$options .= $value. '</option>';
					$options .= PHP_EOL;
				}
			}
			else
			{
				for($i = $params['debut']; $i <= $params['fin']; $i++)
				{
					$options .= ($i == $params['value']) ? '<option selected="selected"' : '<option ';
					if(isset($params['value']) and !empty($params['value'])) $options .= ' value="' .$params['value']. '"';
					$options .= '>' .$i. '</option>';
				}
				unset($params['debut'], $params['fin']);
			}
						
			$this->render .= '<select ';
			unset($params['select'], $params['value']);
			foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
			$this->render .= '>' .$options. '</select>';
		}
		
		if($stateField) $this->render .= "</dd>" .PHP_EOL. "\t</dl>";
	}
	
	/* #######################################
	############## RACCOURCIS ################
	######################################## */

	// Raccourcis personnels
	function addEdit()
	{
		$diff = isset($_GET['edit']) ? $_GET['edit'] : 'add';
		$this->addHidden('edit', $diff);
	}
	function addDate($name, $label = '', $value = '--', $additionalParams = '')
	{
		if($value = '--') $value = date('Y-m-d');
		$valueDate = explode('-', $value);
		
		$this->manualField($name);
		$this->addSelect($name. '_jour', 31, '', '', $valueDate[2], $additionalParams);
		$this->addSelect($name. '_mois', 12, '', '', $valueDate[1], $additionalParams);
		$this->addSelect($name. '_annee', 15, date('Y'), '', $valueDate[0], $additionalParams);
		$this->closeManualField();
	}
	function addHour($name, $label = '', $value = '-', $additionalParams = '')
	{
		$valueDate = explode('-', $value);
		$this->manualField($name);
		$this->addSelect($name. '_hour', 10, 9, $valueDate[0], $additionalParams);
		$this->addSelect($name. '_min', 59, 0, $valueDate[1], $additionalParams);
		$this->closeManualField();
	}
	
	// Raccourcis généraux
	function addSelect($name, $option, $startingValue = '', $label= '', $value = '', $additionalParams = '')
	{
		$additionalParams['fin'] = ($startingValue == '') ? $option : $startingValue + $option;
		$additionalParams['debut'] = $startingValue;
		if($startingValue === '') $additionalParams['debut'] = 1;
		
		$this->addElement($label, $name, "select", $value, $additionalParams);
	}
	function addList($name, $array, $label = '', $value = '', $additionalParams = '')
	{
		$additionalParams['select'] = $array;
		$this->addElement($label, $name, "select", $value, $additionalParams);
	}
	function addRadio($name, $number, $label = '', $value = '', $additionalParams = '')
	{
		$additionalParams['number'] = $number;
		$this->addElement($label, $name, "radio",  $value, $additionalParams);
	}
	function addText($name, $label = '', $value = '', $additionalParams = '')
	{
		$this->addElement($label, $name, "text",  $value, $additionalParams);
	}
	function addHidden($name, $value = '', $additionalParams = '')
	{
		$this->addElement('', $name, "hidden",  $value, $additionalParams);
	}
	function addTextarea($name, $label = '', $value = '', $additionalParams = '')
	{
		$this->addElement($label, $name, "textarea", $value, $additionalParams);
	}
	function addSubmit($name = 'Valider', $label = '', $value = '', $additionalParams = '')
	{
		$this->addElement($label, $name, "submit", $value, $additionalParams);
	}
	function addFile($name, $label = '', $value = '', $additionalParams = '')
	{
		$this->render = str_replace('<form method' ,'<form enctype="multipart/form-data" method', $this->render);
		$this->addElement($label, $name, "file", $value, $additionalParams);
	}
}
?>