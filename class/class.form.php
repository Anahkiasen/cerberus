<?php	
class form
{	
	private $render;
	protected static $valuesArray;
	
	// Etats
	protected static $openedManual = false;
	protected static $mandatory = false;
	
	// Options
	protected static $multilangue = true;
	protected static $formType = 'ilec';
	
	/*
	########################################
	###### METHODES DE CONSTRUCTION ########
	######################################## 
	*/
	
	// Construction
	function __construct($multilangue = NULL, $params = NULL)
	{
		self::$multilangue = (isset($multilangue)) ? $multilangue : MULTILANGUE;

		$this->render = '<form method="post"';
		if(is_array($params) and !empty($params)) $this->render .= a::simplode(array('="', '"'), ' ', $params);
		$this->render .= '>';
	}
	
	// Rendu du formulaire
	function __toString()
	{
		$this->render .= '</form>';
		return $this->render;
	}
	
	// Récupérer les valeurs à partir de la liste des champs
	function getValues($fieldsTable)
	{
		if(isset($fieldsTable[1])) $this->table = $fieldsTable[1];
		if(isset($_GET['edit_' .$fieldsTable[1]]))
		{
			$modif = db::row($fieldsTable[1], implode(',', $fieldsTable[0]), array($fieldsTable[0][0] => $_GET['edit_' .$fieldsTable[1]]));
			foreach($fieldsTable[0] as $value) $post[$value] = stripslashes($modif[$value]); 
		}
		else foreach($fieldsTable[0] as $value) $post[$value] = NULL;
		
		if(isset($_POST)) foreach($fieldsTable[0] as $value)
			if(isset($_POST[$value]) && !empty($_POST[$value])) $post[$value] = stripslashes($_POST[$value]);
			
		self::$valuesArray = $post;
	}
	
	// Passer les valeurs à autrui
	function passValues()
	{
		return self::$valuesArray;
	}
	
	// Ajoute une valeur au tableau
	function addValue($key, $value)
	{
		self::$valuesArray[$key] = $value;
	}
	
	// Changer le type de formulaire
	function setType($type)
	{
		self::$formType = $type;
	}
	
	/*
	########################################
	######## FIELDSETS ET CHAMPS ###########
	######################################## 
	*/
	
	// Ouvrir et fermer un fieldset
	function openFieldset($name, $mandatory = false)
	{
		$fieldName = (!self::$multilangue) ? $name : l::get('form-' .$name);
		self::$mandatory = $mandatory;
		
		$this->render .= PHP_EOL. "
		<fieldset class=\"" .str::slugify($name). "\">" .PHP_EOL. "
			\t<legend>" .$fieldName. '</legend>';
		
		if(self::$openedManual) self::$openedManual = false;
	}
	function closeFieldset()
	{
		if(self::$openedManual) $this->closeManualField();
		$this->render .= PHP_EOL. '</fieldset>';
	}
	
	// Champs manuels
	function manualField($name, $full = false)
	{
		$fieldName = (!self::$multilangue) ? $name : l::get('form-' .$name);
		$mandatoryStar = (self::$mandatory)
			? ' <span class="mandatory">*</span>'
			: '';
		
		if(self::$formType != 'plain')
		{
			$this->render .= '<dl>';
			if(!$full)	$this->render .= '<dt><label for="' .$name. '">' .$fieldName.$mandatoryStar. '</label></dt>';
			$this->render .= '<dd>';
		}
			
		self::$openedManual = true;
	}
	function closeManualField()
	{
		if(self::$formType != 'plain') $this->render .= '</dd></dl>';
		self::$openedManual = false;
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
	function addElement($label, $name, $type, $value = NULL, $additionalParams = NULL)
	{
		$params = array("label" => $label, 'value' => $value, "type" => $type, "name" => $name);
		if(!empty($additionalParams) && is_array($additionalParams)) foreach($additionalParams as $key => $value) $params[$key] = $value;
			
		$this->attachElement($params);
	}
	// Définitions
	function defineNameLabel($name, $label)
	{
		$thisLabel = (empty($label))
			? $name
			: $label;
		if(self::$multilangue == false)
		{
			$thisLabel = ucfirst($thisLabel);
			$thisName = str::slugify(str_replace('-', '', $name));
		}
		else $thisName = $name;
				
		return array($thisName, $thisLabel);
	}
	function defineValue($thisName)
	{
		if(isset($_POST[$thisName])) return stripslashes($_POST[$thisName]);
		if(isset(self::$valuesArray[$thisName])) return self::$valuesArray[$thisName];
	}
	
	// -------------
	// MOTHER METHOD
	// -------------
	function attachElement($params)
	{
		// Définitions	
		$type = $params['type'];
		$name_unsan = $params['name'];
		list($params['name'], $label) = $this->defineNameLabel($params['name'], $params['label']);
		if(empty($params['value'])) $params['value'] = $this->defineValue($name_unsan);
		
		// State Fieldset
		$stateField = (self::$openedManual == false 
		and $type != 'hidden' 
		and self::$formType != 'plain');
		
		// Ouverture du champ
		if($stateField)
		{
			$fieldName = (self::$multilangue == false) ? $label : l::get('form-' .$label);
			$mandatoryStar = (self::$mandatory)
				? ' <span class="mandatory">*</span>'
				: '';
				
			// Champ sous le label plutôt qu'à droite
			$underfield = (isset($params['underfield'])) 
				? 'underfield'
				: NULL;
			unset($params['underfield']);
	
			$this->render .= PHP_EOL. "
				\t<dl class=\"$type $underfield\">" .PHP_EOL;
			if($type != "submit") $this->render .= "\t\t<dt><label for=\"$label\">$fieldName$mandatoryStar</label></dt>" .PHP_EOL;
			$this->render .= "\t\t<dd>";
		}
		
		// Suppression des paramètres inutiles		
		unset($params['label'], $params['type']);
		
		// LISTE DES CHAMPS
		if($type == 'text' or $type == 'password')
		{
			$this->render .= '<input type="' .$type. '" ';
			foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
			$this->render .= ' />';
		}
		if($type == 'submit')
		{
			$fieldName = (self::$multilangue == false) ? $label : l::get('form-submit-' .$label);
			if(self::$formType != 'plain') $this->render .= '<p style="text-align:center"><input type="submit" value="' .$fieldName. '" /></p>';
			else $this->render .= '<input type="submit" value="' .$fieldName. '" />';
		}
		if($type == 'textarea')
		{
			if(isset($params['bbcode'])) $this->render .= '<div class="bbcode">' .file_get_contents('assets/php/bbcode.php'). '</div>';
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
			if(!is_array($params['number']))
				for($i = 0; $i <= $params['number']; $i++)
				{	
					$this->render .= '<input type="radio" ';
					foreach($params as $key => $value) if($key != 'value' && $key != 'number') $this->render .= $key. '="' .$value. '" ';
					$fieldName = (!self::$multilangue) ? $label : l::get('form-' .$label. '-'.$i);
					if($params['value'] == $i) $this->render .= 'checked="checked"';
					$this->render .= ' value="' .$i. '"> ' .$fieldName;
				}
			else
				foreach($params['number'] as $thislabel => $thisvalue)
				{
					$this->render .= '<input type="radio" ';
					foreach($params as $key => $value) if($key != 'value' && $key != 'number') $this->render .= $key. '="' .$value. '" ';
					if($params['value'] == $thisvalue) $this->render .= 'checked="checked"';
					$this->render .= ' value="' .$thisvalue. '"> ' .$thislabel;
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
		$diff = isset($_GET['edit_' .$this->table]) ? $_GET['edit_' .$this->table] : 'add';
		$this->addHidden('edit', $diff);
	}
	function addDate($name = 'Date', $date = NULL)
	{
		$select = new select();
		$select->newSelect($name);
		$select->appendList($select->liste_date($date));
		$this->render .= $select;
	}
	function addHour($name = 'Heure', $hour = NULL)
	{
		$select = new select();
		$select->newSelect($name);
		$select->appendList($select->liste_heure($hour));
		$this->render .= $select;
	}
	
	// Raccourcis généraux
	function addRadio($name, $number, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$additionalParams['number'] = $number;
		$this->addElement($label, $name, "radio", $value, $additionalParams);
	}
	function addText($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addElement($label, $name, "text", $value, $additionalParams);
	}
	function addPass($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addElement($label, $name, "password", $value, $additionalParams);
	}
	function addHidden($name, $value = NULL, $additionalParams = NULL)
	{
		$this->addElement('', $name, "hidden", $value, $additionalParams);
	}
	function addTextarea($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addElement($label, $name, "textarea", $value, $additionalParams);
	}
	function addSubmit($name = 'Valider', $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->addElement($label, $name, "submit", $value, $additionalParams);
	}
	function addFile($name, $label = NULL, $value = NULL, $additionalParams = NULL)
	{
		$this->render = str_replace('<form method' ,'<form enctype="multipart/form-data" method', $this->render);
		$this->addElement($label, $name, "file", $value, $additionalParams);
	}
}
/* 
########################################
############# CREATION SELECT ##########
########################################
 
Syntaxe 
$select = new select;
	$select->newSelect(LABEL);
	$select->appendList(LISTE[array/liste_date/liste_number);
	$form->insertText($select);
*/
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
	function __construct($name = NULL)
	{	
		if(!isset(self::$valuesArray)) self::$valuesArray = array();
		if(!empty($name)) $this->newSelect($name);
	}
		
	// Initialisation
	function newSelect($name, $label = NULL)
	{
		$this->render = NULL;
		$this->liste = 
		$this->params = array();
		
		list($this->name, $this->label) = $this->defineNameLabel($name, $label);
	}
	
	// Accrochage de la liste au <select>
	function appendList($liste, $overwrite = true)
	{
		if($overwrite)
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
	
	// Rendu
	function __toString()
	{
		if(empty($this->render)) $this->createElement();
		return $this->render;
	}
	
	/*
	Options 
	*/
	
	// Ajout de paramètres
	function addParams($params = NULL)
	{
		$this->params += $params;
	}
	
	// Régler la valeur du select sur
	function setValue($value)
	{
		$this->value = $value;
	}
		
	/* 
	########################################
	############## RACCOURCIS ##############
	########################################
	*/

	// Liste à chiffres
	function liste_number($end, $start = 0, $step = 1)
	{
		return range($start, $end, $step);
	}
	
	// Array manuel
	function liste_array($list)
	{
		return $list;
	}
	
	// Champ date
	function liste_date($date = NULL, $startingYear = 2010)
	{
		// Date dans les valeurs données ou manuelle, sinon date actuelle
		if(isset(self::$valuesArray[strtolower($this->name)])) $date = self::$valuesArray[strtolower($this->name)];
		if(empty($date)) $date = date('Y-m-d');
		$valueDate = explode('-', $date);
		$this->params['class'] = 'dateForm';
		
		// On inscrit la date décomposée dans les valeurs
		self::$valuesArray = array(
		$this->name. '_jour' => $valueDate[2],
		$this->name. '_mois' => $valueDate[1],
		$this->name. '_annee' => $valueDate[0]);
		
		// Création des trois listes correspondantes
		return array(
		$this->name. '_jour' => $this->liste_array($this->liste_number(31, 1)),
		$this->name. '_mois' => $this->liste_array($this->liste_number(12, 1)),
		$this->name. '_annee' => $this->liste_array($this->liste_number((date('Y')+10), $startingYear)));
	}
	
	// Champ heure
	function liste_heure($hour = NULL)
	{
		if(empty($hour)) $hour = '-';
		$valueHour = explode('-', $hour);
		$this->params['class'] = 'dateForm';
		
		self::$valuesArray = array(
		$this->name. '_hour' => $valueHour[0],
		$this->name. '_min' => $valueHour[1]);
		
		return array(
		$this->name. '_hour' => $this->liste_array($this->liste_number(0, 24)),
		$this->name. '_min' => $this->liste_array($this->liste_number(59, 0)));
	}
	
	/* 
	########################################
	############## RENDU ###################
	########################################
	*/
	
	// Création du champ
	function createElement()
	{			
		$label = $this->label;
		$stateField = (!self::$openedManual 
		and self::$formType != 'plain');
		
		// Ouverture du champ
		if($stateField)
		{
			$fieldName = (!self::$multilangue) ? $label : l::get('form-' .$label);
			$mandatoryStar = (self::$mandatory)
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
		// Création d'un <select> (sous-fonction de createElement)
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
				else $selected = NULL;
				
				if($key == $value) $this->render .= '<option ' .$selected. '>' .$value. '</option>';
				else $this->render .= '<option value="' .$key. '" ' .$selected. '>' .$value. '</option>';
			}
				
			$this->render .= '</select>';
		}
}
?>