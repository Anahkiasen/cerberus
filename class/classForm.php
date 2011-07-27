<?php	
class form
{
	private $render;
	
	// Etats
	private $openedManual = false;
	private $mandatory = false;
	
	// Options
	private $multilangue = false;
	private $formType = 'ilec';
	
	/* ########################################
	####### METHODES DE CONSTRUCTION ##########
	######################################## */
	
	// Construction
	function __construct($multilangue = false, $params = '')
	{
		$this->multilangue = $multilangue;
		$this->render = '<form method="post"';
		if(is_array($params) and !empty($params)) foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
		$this->render .= '>';
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
	function getParent()
	{
		if(!isset($this->valuesArray)) $this->valuesArray = array();
		return array($this->multilangue, $this->formType, $this->mandatory, $this->openedManual, $this->valuesArray);
	}
	
	// Fieldsets
	function openFieldset($name, $mandatory = false)
	{
		$fieldName = ($this->multilangue == false) ? $name : index('form-' .$name);
		$this->mandatory = $mandatory;
		
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
		$mandatoryStar = ($this->mandatory == true)
			? ' <span class="mandatory">*</span>'
			: '';
		
		if($this->formType != 'plain')
		{
			$this->render .= '<dl>';
			if($full == false)	$this->render .= '<dt><label for="' .$name. '">' .$fieldName.$mandatoryStar. '</label></dt>';
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
	function insertDText($text)
	{
		$this->render .= '<dl><dt>' .$text. '</dt></dl>';
	}
	
	/* #######################################
	######### CREATION DE L'ELEMENT ##########
	######################################## */
	
	// fieldset > dl > dt label > dd input
	
	// Fonction moteur
	function addElement($label, $name, $type, $value = '', $additionalParams = '')
	{
		$params = array("label" => $label, 'value' => $value, "type" => $type, "name" => $name);
		if(!empty($additionalParams) && is_array($additionalParams)) foreach($additionalParams as $key => $value) $params[$key] = $value;
			
		$this->attachElement($params);
	}
	// Définitions
	function defineNameLabel($name, $label)
	{
		$thisLabel = (empty($label))
			? ucfirst($name)
			: $label;
		if($this->multilangue == true) $thisLabel = strtolower($thisLabel);
		$thisName = normalize(str_replace('-', '', $name));
		
		return array($thisName, $thisLabel);
	}
	function defineValue($thisName)
	{
		if(isset($_POST[$thisName])) return stripslashes($_POST[$thisName]);
		if(isset($this->valuesArray[$thisName])) return $this->valuesArray[$thisName];
	}
	
	// -------------
	// MOTHER METHOD
	// -------------
	function attachElement($params)
	{
		global $index;
			
		// Définitions	
		$type = $params['type'];
		list($name, $label) = $this->defineNameLabel($params['name'], $params['label']);
		if(empty($params['$value'])) $params['value'] = $this->defineValue($params['name']);
		
		// State Fieldset
		$stateField = ($this->openedManual == false 
		and $type != 'hidden' 
		and $this->formType != 'plain');
		
		// Ouverture du champ
		if($stateField)
		{
			$fieldName = ($this->multilangue == false) ? $label : index('form-' .$label);
			$mandatoryStar = ($this->mandatory == true)
				? ' <span class="mandatory">*</span>'
				: '';
	
			$this->render .= PHP_EOL. "
				\t<dl class=\"$type\">" .PHP_EOL;
			if($type != "submit") $this->render .= "\t\t<dt><label for=\"$label\">$fieldName$mandatoryStar</label></dt>" .PHP_EOL;
			$this->render .= "\t\t<dd>";
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
			if(isset($params['bbcode'])) $this->render .= file_get_contents('pages/scripts-bbcode.php');
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
	function addDate($name = 'Date', $date = '')
	{
		$select = new select();
		$select->newSelect($name);
		$select->appendList($select->liste_date($date));
		$this->render .= $select;
	}
	function addHour($name = 'Heure', $hour = '')
	{
		$select = new select();
		$select->newSelect($name);
		$select->appendList($select->liste_heure($hour));
		$this->render .= $select;
	}
	
	// Raccourcis généraux
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
/* #######################################
############## CREATION SELECT ###########
######################################## */
class select extends form
{
	// Select
	private $params = array();
	private $liste = array();
	private $name;
	private $label;
	private $value;
		
	private $render;

	// Construction
	function __construct()
	{
		list($this->multilangue, $this->formType, $this->mandatory, $this->openedManual, $this->valuesArray) = $this->getParent();
	}
	function __toString()
	{
		if(empty($this->render)) $this->createElement();
		return $this->render;
	}
	function newSelect($name, $label = '')
	{
		$this->render = '';
		$this->liste = 
		$this->params = array();
		
		list($this->name, $this->label) = $this->defineNameLabel($name, $label);
	}
	function addParams($params = '')
	{
		$this->params += $params;
	}
	function setValue($value)
	{
		$this->value = $value;
	}
	
	// Valeur du select
	function appendList($liste, $overwrite = true)
	{
		if($overwrite == true)
		{
			foreach($liste as $key => $value) 
				if(!is_array($value)) $thisListe[$value] = $value;
				else
				{
					unset($newArray);
					foreach($value as $skey => $svalue) $newArray[$svalue] = $svalue;
					$thisListe[$key] = $newArray;
				}
		}
		else $thisListe = $liste;
		$this->liste += $thisListe;
	}
	function liste_number($end, $start = 0, $step = 1)
	{
		return range($start, $end, $step);
	}
	function liste_array($list)
	{
		return $list;
	}
	function liste_date($date = '')
	{
		if(empty($date)) $date = date('Y-m-d');
		$valueDate = explode('-', $date);
		$this->params['class'] = 'dateForm';
		
		$this->valuesArray = array(
		$this->name. '_jour' => $valueDate[2],
		$this->name. '_mois' => $valueDate[1],
		$this->name. '_annee' => $valueDate[0]);
		
		return array(
		$this->name. '_jour' => $this->liste_array($this->liste_number(31, 1)),
		$this->name. '_mois' => $this->liste_array($this->liste_number(12, 1)),
		$this->name. '_annee' => $this->liste_array($this->liste_number(date('Y'), (date('Y')-10))));
	}
	function liste_heure($hour = '')
	{
		if(empty($hour)) $hour = '-';
		$valueHour = explode('-', $hour);
		$this->params['class'] = 'dateForm';
		
		$this->valuesArray = array(
		$this->name. '_hour' => $valueHour[0],
		$this->name. '_min' => $valueHour[1]);
		
		return array(
		$this->name. '_hour' => $this->liste_array($this->liste_number(0, 24)),
		$this->name. '_min' => $this->liste_array($this->liste_number(59, 0)));
	}
	
	// Création d'un <select>
	function createSelect($name, $liste)
	{
		$thisValue = (empty($this->value))
			? $this->defineValue($name)
			: $this->value;

		$this->render .= '<select name="' .$name. '" ';
		foreach($this->params as $key => $value) $this->render .= $key. '="' .$value. '"';
		$this->render .= '>';
		
		foreach($liste as $key => $value)
		{
			if($key == $value and $thisValue == $value) $selected = 'selected="selected"';
			elseif($key != $value and $thisValue == $key) $selected = 'selected="selected"';
			else $selected = '';
			
			if($key == $value) $this->render .= '<option ' .$selected. '>' .$value. '</option>';
			else $this->render .= '<option value="' .$key. '" ' .$selected. '>' .$value. '</option>';
		}
			
		$this->render .= '</select>';
	}
	// Création du champ
	function createElement()
	{
		global $index;
			
		$label = $this->label;
		$stateField = ($this->openedManual == false 
		and $this->formType != 'plain');
		
		// Ouverture du champ
		if($stateField)
		{
			$fieldName = ($this->multilangue == false) ? $label : index('form-' .$label);
			$mandatoryStar = ($this->mandatory == true)
				? ' <span class="mandatory">*</span>'
				: '';
	
			$this->render .= '<dl class="select">
			<dt><label for="' .$label. '">' .$fieldName.$mandatoryStar. '</label></dt>
			<dd>';
		}
		
		// Rendu
		$lol = array_values($this->liste);
		if(is_array($lol[0]))
		{
			foreach($this->liste as $key => $value)
				$this->createSelect($key, $value);
		}
		else $this->createSelect($this->name, $this->liste);
				
		if($stateField) $this->render .= '</dd></dl>';	
	}	
}
?>