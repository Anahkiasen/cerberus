<?php
	
/*@ ########################################
function52[CLASSE FORM - {3}] 
######################################## @*/
class form
{
	private $render;
	private $openStat;
	private $multi;
	
	public function __construct($method, $fieldUnder = false, $multiL = true)
	{
		$this->multi = $multiL;
		$class = ($fieldUnder == true) ? 'class="fieldUnder"' : '';
		$this->render .= '<form method="' .$method. '" ' .$class. '>';
	}
	function __toString()
	{
		$this->render .= '</form>';
		return $this->render;
	}
	
	function openFieldset($name)
	{
		$fieldName = ($this->multi == false) ? $name : index('form-' .$name);
		$this->render .= PHP_EOL. "<fieldset>" .PHP_EOL. "\t<legend>" .$fieldName. '</legend>';
		if($this->openStat == true) $this->openStat = false;
	}
	function closeFieldset()
	{
		if($this->openStat == true) $this->closeManual();
		$this->render .= PHP_EOL. '</fieldset>';
	}
	function openManual($name)
	{
		$fieldName = ($this->multi == false) ? $name : index('form-' .$name);
		$this->render .= '<dl><dt><label for="' .$name. '">' .$fieldName. '</label></dt><dd>';
		$this->openStat = true;
	}
	function closeManual()
	{
		$this->render .= '</dd></dl>';
		$this->openStat = false;
	}
	function insertText($text)
	{
		$this->render .= $text;
	}
	
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
		$label = $params['label'];
		if(empty($params['label'])) $label = $params['name'];
		$params['name'] = normalize(str_replace('-', '', $params['name']));
		if(isset($_POST[$params['name']]) && empty($params['value'])) $params['value'] = stripslashes($_POST[$params['name']]);
		if(isset($this->valuesArray[$params['name']]) && empty($params['value'])) $params['value'] = $this->valuesArray[$params['name']];
		
		if($this->openStat == false and $type != "hidden")
		{
			$this->render .= PHP_EOL. "\t";
			$this->render .= '<dl class="' .$type. '">';
			$this->render .= PHP_EOL. "\t";
			if(!empty($label) && $type != 'submit')
			{
				$fieldName = ($this->multi == false) ? $label : index('form-' .$label);
				$this->render .= "\t";
				$this->render .= '<dt><label for="' .$label. '">' .$fieldName. '</label></dt>';
				$this->render .= PHP_EOL. "\t\t";
				$this->render .= "<dd>";
			}
			else $this->render .= PHP_EOL. "\t<dd style=\"float: none; width: 100%\">";
		}
		
		unset($params['label'], $params['type']);
		
		if($type == 'text')
		{
			$this->render .= '<input type="text" ';
			foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
			$this->render .= ' />';
		}
		if($type == 'submit')
		{
			$fieldName = ($this->multi == false) ? $label : index('form-submit-' .$label);
			$this->render .= '<p style="text-align:center"><input type="submit" value="' .$fieldName. '" /></p>';
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
				$fieldName = ($this->multi == false) ? $label : index('form-' .$label. '-'.$i);
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
					$options .= ' value="' .$params['value']. '">' .$i. '</option>';
				}
				unset($params['debut'], $params['fin']);
			}
						
			$this->render .= '<select ';
			unset($params['select'], $params['value']);
			foreach($params as $key => $value) $this->render .= $key. '="' .$value. '" ';
			$this->render .= '>' .$options. '</select>';
		}
		
		if($this->openStat == false and $type != "hidden") $this->render .= "</dd>" .PHP_EOL. "\t</dl>";
	}
	
	// Raccourcis
	function addEdit()
	{
		$diff = isset($_GET['edit']) ? $_GET['edit'] : 'add';
		$this->addHidden('edit', $diff);
	}
	function addDate($name, $label = '', $value = '--', $additionalParams = '')
	{
		$valueDate = explode('-', $value);
		$this->openManual($name);
		$this->addSelect($name. '_jour', 31, '', '', $valueDate[2], $additionalParams);
		$this->addSelect($name. '_mois', 12, '', '', $valueDate[1], $additionalParams);
		$this->addSelect($name. '_annee', 15, date('Y'), '', $valueDate[0], $additionalParams);
		$this->closeManual();
	}
	function addHour($name, $label = '', $value = '-', $additionalParams = '')
	{
		$valueDate = explode('-', $value);
		$this->openManual($name);
		$this->addSelect($name. '_hour', 10, 9, $valueDate[0], $additionalParams);
		$this->addSelect($name. '_min', 59, 0, $valueDate[1], $additionalParams);
		$this->closeManual();
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
	function addSubmit($name = '', $label = '', $value = '', $additionalParams = '')
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