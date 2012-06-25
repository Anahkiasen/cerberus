<?php
class form
{
	private $render;
	protected static $valuesArray;

	// Etats
	protected static $openedManual = false;
	protected static $mandatory    = false;

	// Options
	protected static $multilangue  = true;
	protected static $formType     = 'ilec';

	/*
	########################################
	###### METHODES DE CONSTRUCTION ########
	########################################
	*/

	// Construction
	public function __construct($multilangue = null, $params = null)
	{
		self::$multilangue = (isset($multilangue)) ? $multilangue : MULTILANGUE;

		$this->render = '<form method="post"';
		if(is_array($params) and !empty($params)) $this->render .= a::glue($params, ' ', '="', '"');
		$this->render .= '>';
	}

	// Rendu du formulaire
	public function __toString()
	{
		$this->render .= '</form>';
		return $this->render;
	}

	// Récupérer les valeurs à partir de la liste des champs
	public function getValues($fieldsTable)
	{
		if(isset($fieldsTable[1]))
		{
			$this->table = $fieldsTable[1];
			$this->usable = str_replace('cerberus_', null, $this->table);
		}
		if(isset($_GET['edit_' .$this->usable]))
		{
			$modif = db::row($fieldsTable[1], implode(',', $fieldsTable[0]), array($fieldsTable[0][0] => $_GET['edit_' .$this->usable]));
			foreach($fieldsTable[0] as $value) $post[$value] = stripslashes($modif[$value]);
		}
		else foreach($fieldsTable[0] as $value) $post[$value] = null;

		if(isset($_POST)) foreach($fieldsTable[0] as $value)
			if(isset($_POST[$value]) && !empty($_POST[$value])) $post[$value] = stripslashes($_POST[$value]);

		self::$valuesArray = $post;
	}

	// Passer les valeurs à autrui
	public function passValues()
	{
		return self::$valuesArray;
	}

	// Ajoute une valeur au tableau
	public function addValue($key, $value)
	{
		self::$valuesArray[$key] = $value;
	}

	// Changer le type de formulaire
	public function setType($type)
	{
		self::$formType = $type;
	}

	/*
	########################################
	######## FIELDSETS ET CHAMPS ###########
	########################################
	*/

	// Ouvrir et fermer un fieldset
	public function openFieldset($name, $mandatory = false)
	{
		$fieldName = (!self::$multilangue) ? $name : l::get('form-' .$name);
		self::$mandatory = $mandatory;

		$this->render .= PHP_EOL. "
		<fieldset class=\"" .str::slugify($name). "\">" .PHP_EOL. "
			\t<legend>" .$fieldName. '</legend>';

		if(self::$openedManual) self::$openedManual = false;
	}
	public function closeFieldset()
	{
		if(self::$openedManual) $this->closeManualField();
		$this->render .= PHP_EOL. '</fieldset>';
	}

	// Champs manuels
	public function manualField($name, $full = false)
	{
		$fieldName = (!self::$multilangue) ? $name : l::get('form-' .$name);
		$mandatoryStar = (self::$mandatory)
			? ' <span class="mandatory">*</span>'
			: null;

		if(self::$formType != 'plain')
		{
			$this->render .= '<dl>';
			if(!$full)	$this->render .= '<dt><label for="' .$name. '">' .$fieldName.$mandatoryStar. '</label></dt>';
			$this->render .= '<dd>';
		}

		self::$openedManual = true;
	}
	public function closeManualField()
	{
		if(self::$formType != 'plain') $this->render .= '</dd></dl>';
		self::$openedManual = false;
	}

	// Texte manuel
	public function insertText($text)
	{
		$this->render .= $text;
	}
	public function insertDText($text)
	{
		$this->render .= '<dl><dt>' .$text. '</dt></dl>';
	}

	/* #######################################
	######### CREATION DE L'ELEMENT ##########
	######################################## */

	// fieldset > dl > dt label > dd input

	// Fonction moteur
	public function addElement($label, $name, $type, $value = null, $additionalParams = null)
	{
		$params = array("label" => $label, 'value' => $value, "type" => $type, "name" => $name);
		if(!empty($additionalParams) && is_array($additionalParams)) foreach($additionalParams as $key => $value) $params[$key] = $value;

		$this->attachElement($params);
	}
	// Définitions
	public function defineNameLabel($name, $label)
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
	public function defineValue($thisName)
	{
		if(isset($_POST[$thisName])) return stripslashes($_POST[$thisName]);
		if(isset(self::$valuesArray[$thisName])) return self::$valuesArray[$thisName];
	}

	// -------------
	// MOTHER METHOD
	// -------------
	public function attachElement($params)
	{
		// Définitions
		$type = $params['type'];
		$name_unsan = $params['name'];
		list($params['name'], $label) = $this->defineNameLabel($params['name'], $params['label']);
		if(empty($params['value']) and $params['value'] !== '0') $params['value'] = $this->defineValue($name_unsan);

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
				: null;

			// Champ sous le label plutôt qu'à droite
			$underfield = (isset($params['underfield'])) ? 'underfield' 	: null;
			$underfield = (isset($params['underfield'])) ? 'underfield' 	: null;
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
			if(isset($params['bbcode'])) $this->render .= '<div class="bbcode">' .file_get_contents(PATH_COMMON.'php/bbcode.php'). '</div>';
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
	public function addEdit()
	{
		$diff = isset($_GET['edit_' .$this->usable]) ? $_GET['edit_' .$this->usable] : 'add';
		$this->addHidden('edit', $diff);
	}
	public function addDate($name = 'Date', $date = null)
	{
		$select = new select();
		$select->newSelect($name);
		$select->appendList($select->liste_date($date));
		$this->render .= $select;
	}
	public function addHour($name = 'Heure', $hour = null)
	{
		$select = new select();
		$select->newSelect($name);
		$select->appendList($select->liste_heure($hour));
		$this->render .= $select;
	}

	// Raccourcis généraux
	public function addRadio($name, $number, $label = null, $value = null, $additionalParams = null)
	{
		$additionalParams['number'] = $number;
		$this->addElement($label, $name, "radio", $value, $additionalParams);
	}
	public function addText($name, $label = null, $value = null, $additionalParams = null)
	{
		$this->addElement($label, $name, "text", $value, $additionalParams);
	}
	public function addPass($name, $label = null, $value = null, $additionalParams = null)
	{
		$this->addElement($label, $name, "password", $value, $additionalParams);
	}
	public function addHidden($name, $value = null, $additionalParams = null)
	{
		$this->addElement('', $name, "hidden", $value, $additionalParams);
	}
	public function addTextarea($name, $label = null, $value = null, $additionalParams = null)
	{
		$this->addElement($label, $name, "textarea", $value, $additionalParams);
	}
	public function addSubmit($name = 'Valider', $label = null, $value = null, $additionalParams = null)
	{
		$this->addElement($label, $name, "submit", $value, $additionalParams);
	}
	public function addFile($name, $label = null, $value = null, $additionalParams = null)
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
	public function __construct($name = null)
	{
		if(!isset(self::$valuesArray)) self::$valuesArray = array();
		if(!empty($name)) $this->newSelect($name);
	}

	// Initialisation
	public function newSelect($name, $label = null)
	{
		$this->render = null;
		$this->liste =
		$this->params = array();

		list($this->name, $this->label) = $this->defineNameLabel($name, $label);
	}

	// Accrochage de la liste au <select>
	public function appendList($liste, $overwrite = true)
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
	public function __toString()
	{
		if(empty($this->render)) $this->createElement();
		return $this->render;
	}

	/*
	Options
	*/

	// Ajout de paramètres
	public function addParams($params = null)
	{
		$this->params += $params;
	}

	// Régler la valeur du select sur
	public function setValue($value)
	{
		$this->value = $value;
	}

	/*
	########################################
	############## RACCOURCIS ##############
	########################################
	*/

	// Liste à chiffres
	public function liste_number($end, $start = 0, $step = 1)
	{
		return range($start, $end, $step);
	}

	// Array manuel
	public function liste_array($list)
	{
		return $list;
	}

	// Champ date
	public function liste_date($date = null, $startingYear = 2010)
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
	public function liste_heure($hour = null)
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
	public function createElement()
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
				: null;

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
			$thisValue = ($this->value === "")
				? $this->defineValue($name)
				: $this->value;

			$this->render .= '<select name="' .$name. '" ';
			foreach($this->params as $key => $value) $this->render .= $key. '="' .$value. '"';
			$this->render .= '>';

			foreach($liste as $key => $value)
			{
				if($key == $value and $thisValue == $value) $selected = 'selected="selected"';
				elseif($key != $value and $thisValue == $key) $selected = 'selected="selected"';
				else $selected = null;

				if($key === $value) $this->render .= '<option ' .$selected. '>' .$value. '</option>';
				else $this->render .= '<option value="' .$key. '" ' .$selected. '>' .$value. '</option>';
			}

			$this->render .= '</select>';
		}
}
