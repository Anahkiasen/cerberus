<?php
/**
 *
 * forms
 *
 * This class handles the creation and validation of forms
 *
 * @package Cerberus
 */
namespace Cerberus\Modules;

use Cerberus\Toolkit\Arrays   as a,
    Cerberus\Toolkit\Database as db,
    Cerberus\Toolkit\Language as l,
    Cerberus\Toolkit\Request  as r,
    Cerberus\Toolkit\String   as str;

class Form
{
  // Current state ----------------------------------------------- /

  /**
   * Current depth of indentation
   *
   * @var integer
   */
  private $tabs = 0;

  /**
   * Whether we're currently inside a fieldset or not
   *
   * @var boolean
   */
  private $openedTags = array();

  /**
   * Current values of the different form fields
   *
   * @var array
   */
  private $values = array();

  /**
   * Contains informations about the form validation
   *
   * @var array
   */
  private $status = array();

  /**
   * A list of the fields in the current form
   * @var array
   */
  private $fields = array();

  // Options ----------------------------------------------------- /

  /**
   * Form is multilanguage or not
   *
   * @var boolean
   */
  private $optionMultilangue = false;

  /**
   * Current form type [horizontal|vertical|search|inline]
   *
   * @var string
   */
  private $optionFormType = 'horizontal';

  // Render ------------------------------------------------------ /

  /**
   * Contains the form rendered
   *
   * @var string
   */
  private $render = null;

  //////////////////////////////////////////////////////////////////
  //////////////////////////// CONSTRUCTION ////////////////////////
  //////////////////////////////////////////////////////////////////

  public function __construct($params = null, $multilangue = null)
  {
    // Defining <form> class and method
    if(!is_array($params))        $params = array('class' => $params);
    if(!isset($params['method'])) $params['method'] = 'post';
    if(!isset($params['class']))  $params['class'] = 'form-horizontal';

    // Defining initial depth
    $this->tabs = a::get($params, 'tabs', 1);

    // Multilanguage or not
    $this->optionMultilangue = $multilangue ? $multilangue : MULTILANGUE;

    // Render initial tag
    $this->rend('<form ' .$this->paramRender($params, 'tabs'). '>');

    // Determining form type according to Bootstrap classification
    $formClass = a::get($params, 'class');

        if(str::find('form-horizontal', $formClass)) $this->optionFormType = 'horizontal';
    elseif(str::find('form-vertical',   $formClass)) $this->optionFormType = 'horizontal';
    elseif(str::find('form-search',     $formClass)) $this->optionFormType = 'search';
    elseif(str::find('form-inline',     $formClass)) $this->optionFormType = 'inline';
  }

  public function parseForm($form)
  {
    if(!is_array($form)) $form = str::parse($form, 'json');
    if(!is_array($form)) return false;

    // Read parsed array
    foreach ($form as $fieldset => $value) {
      // If it's a flat form
      if (is_numeric($fieldset)) {
        if(is_array($value)) $this->addElement($value);
        else $this->insert($value);
      }

      // If it's a form divided in fieldsets
      else {
        $this->openFieldset($fieldset);
        foreach ($value as $field) {
          if(is_array($field)) $this->addElement($field);
          else $this->insert($field);
        }
        $this->closeFieldset();
      }
    }
  }

  //////////////////////////////////////////////////////////////////
  //////////////////////////// VALIDATION //////////////////////////
  //////////////////////////////////////////////////////////////////

  // If a form was sent, check the fields and returned validated values
  public function validate()
  {
    if(!isset($_POST) or empty($_POST)) return false;

    // Getting validation masks
    $parser   = func_get_args();

    // Creating a body for the mail if we send one
    $mailBody = null;

    // Array containing filtered values
    $result   = array();

    // Array containing encountered errors
    $errors   = array();

    // Analyze the form fields
    foreach ($parser as $field) {
      $params                = explode(':', $field);
      $key                   = a::get($params, 0);
      $type                  = a::get($params, 1, $key);
      $default               = a::get($params, 2, null);
      $value                 = $type == 'file'
                                 ? a::get($_FILES, $key)
                                 : str::sanitize(r::post($key, $default), $type);

      $status                = (valid::check($value, $type) and a::get($value, 'error', 0) == 0);
      $this->status[$key]    = $status;

      $result[$key]          = $value;
      if(!$status) $errors[] = $key;
    }

    // Display any found errors
    if (!empty($errors)) {
      $empty = array();
      foreach($this->status as $field => $value)
        if(!$value) $empty[] = a::get($this->fields, $field.',label');
        $message = l::get('form.incomplete'). ' :<br />' .implode(', ', $empty);

      return array(
        'msg'    => $message,
        'result' => $result,
        'state'  => $this->status,
        'status' => false);
    }

    // Create the mail body
    $mailBody = Mail::flatten($result);

    // Return validated values
    return array(
      'status' => true,
      'result' => $result,
      'state'  => $this->status,
      'mail'   => $mailBody);
  }

  //////////////////////////////////////////////////////////////////
  //////////////////////////// WRAPPERS ////////////////////////////
  //////////////////////////////////////////////////////////////////

  public function wrap($tagHash, $attributes = array())
  {
    $tagHash = explode('.', $tagHash);

    // Getting tag, classes and ID
    $class = a::remove($tagHash, 0);
    $tag   = a::get($tagHash, 0);

    // Writing classes
    $attributes = array_merge($attributes, array('class' => implode(' ', $class)));

    // Writing tag
    $this->opened[] = $tag;
    $this->tab('<' .$tag. ' ' .$this->paramRender($attributes). '>');
  }

  public function close($tag = null)
  {
    if(empty($this->opened)) return true;

    // Getting opened tag
    if(!$tag) $tag = end($this->opened);
    $this->untab('</' .$tag. '>');
  }

  // Open a fieldset
  public function openFieldset($name)
  {
    $this->wrap('fieldset.'.str::slugify($name));
    $this->rend('<legend>' .l::get('form-'.$name, ucfirst($name)). '</legend>');
  }

  // Close a fieldset
  public function closeFieldset()
  {
    $this->close('fieldset');
  }

  //////////////////////////////////////////////////////////////////
  ///////////////////////////// VALUES /////////////////////////////
  //////////////////////////////////////////////////////////////////

  // Modify the values array
  public function values($table)
  {
    $this->usable = str_replace('cerberus_', null, $table);
    $id = db::fields($table);
    $id = in_array('id', $id) ? 'id' : a::get($id, 0);

    if(isset($_GET['edit_'.$this->usable]))
      $this->values = db::row($table, '*', array($id => $_GET['edit_'.$this->usable]));
  }

  // Set several values
  public function setValues($array)
  {
    foreach($array as $key => $val) $this->values[$key] = $val;
  }

  // Set a single value
  public function setValue($key, $value)
  {
    $this->values[$key] = $value;
  }

  // Gets the readable name of a field
  public static function getReadable($field)
  {
    return ucfirst(l::get('form-' .$field, $field));
  }

  //////////////////////////////////////////////////////////////////
  ///////////////////////////// BUILDERS ///////////////////////////
  //////////////////////////////////////////////////////////////////

  // Add an element to the form
  public function addElement($params)
  {
    // Fetch the building array, as a PHP array or JSON
    if (is_array($params)) {
      $params = array_merge($params, a::get($params, 'params', array()));
      $params = a::remove($params, 'params');
    } else $params = str::parse('{'.$params.'}', 'json');

    /////////////////////////
    // PARAMÈTRES DU CHAMP //
    /////////////////////////

    // Type du champ
    $deploy['type'] = a::get($params, 'type', 'text');

    // Label du champ
    $label = a::get($params, 'label', self::getReadable(a::get($params, 'name')));

    // Attribut name
    $deploy['name'] =   a::get($params, 'name',
                         str::slugify($label));
    $deploy['name'] =   l::get($deploy['name'],
                         $deploy['name']);

    // Valeur du champ
    $issetPost = isset($_POST) ? $_POST : null;
    $deploy['value'] =  a::get($issetPost, $deploy['name'],
              a::get($params, 'value',
              a::get($this->values, $deploy['name'])));

    // Paramètres auxiliaires et data-*
    $auxiliaires = array('placeholder', 'pattern', 'min', 'max', 'step', 'style', 'rel', 'rows', 'id', 'disabled', 'select');
    foreach($params as $key => $value)
      if(in_array($key, $auxiliaires) or str::find('data-', $key)) $deploy[$key] = $value;
      if(a::get($deploy, 'data-provide') == 'typeahead') $deploy['autocomplete'] = 'off';

    // Champ obligatoire
    $mandatory = a::get($params, 'mandatory');
    if(isset($params['multiple'])) $deploy['multiple'] = 'multiple';

    // Listes
    $checkboxes  = a::get($params, 'checkboxes');
    $radio       = a::get($params, 'radio');

    // Add-ons
    $prepend     = a::get($params, 'prepend');
    $append      = a::get($params, 'append');
    $prependType = $prepend ? 'prepend' : 'append';
    $addon       = a::get($params, 'addon');

    // Classe du champ
    $deploy['class'] = a::get($params, 'class');
    if(is_array($deploy['class'])) $deploy['class'] = implode(' ', $deploy['class']);
    if($addon) $deploy['class'] .= ' ' .$addon;

    $divClass   = $deploy['type'] == 'submit' ? array('form-actions') : array('control-group');
    $divClass[] = a::get($params, 'status', a::get($this->status, $deploy['name']));
    $divClass[] = str::slugify($label);
    $divClass[] = str::slugify($deploy['type']);
    if($mandatory) $divClass[] = 'mandatory';

    // Add field to global array
    $this->fields[$deploy['name']] = array_merge($deploy, array('label' => $label));

    ////////////////////
    /////// RENDU //////
    ////////////////////

    $openDiv = ($this->optionFormType == 'horizontal' and $deploy['type'] != 'hidden');

    if($openDiv)
      $this->rend('<div class="' .implode(' ', $divClass). '">', 'TAB');

      // LABEL
      if(!in_array($deploy['type'], array('submit', 'checkbox', 'hidden')) and $this->optionFormType != 'search')
        $this->rend('<label for="' .$deploy['name']. '" class="control-label">' .$label. '</label>');

      // DIV ENGLOBANTE
      $englobe = ($deploy['type'] != 'submit' and $this->optionFormType == 'horizontal');
      if($englobe) $this->rend('<div class="controls">', 'TAB');
      if($prepend or $append) $this->rend('<div class="input-' .$prependType. '">', 'TAB');

      // CHAMP MÊME
      if($prepend) $this->rend('<span class="add-on">' .$prepend. '</span>', 'TAB');
      switch ($deploy['type']) {
        // Texte
        case 'date':
        case 'email':
        case 'hidden':
        case 'number':
        case 'password':
        case 'search':
        case 'tel':
        case 'text':
          if($addon == 'uneditable-input') $this->rend('<span ' .$this->paramRender($deploy, 'value'). '>' .$deploy['value']. '</span>');
          elseif($addon == 'disabled') $this->rend('<input ' .$this->paramRender($deploy, 'value'). ' placeholder="' .$deploy['value']. '" disabled />');
          else $this->rend('<input ' .$this->paramRender($deploy). ' />');
          break;

        case 'textarea':
          $this->rend('<textarea ' .$this->paramRender($deploy, 'value'). '>' .$deploy['value']. '</textarea>');
          break;

        case 'checkbox':
          $this->rend('<label class="checkbox">');
          $checked = isset($_POST[a::get($deploy, 'name')]) ? ' checked="checked"' : null;
          $this->rend('<input ' .$this->paramRender($deploy).$checked. ' /> '.$label);
          $this->rend('</label>');
          break;

        // Listes
        case 'checkboxes':
          $nameCheckbox = a::get($params, 'name').'[]';
          $postCheckbox = r::post($deploy['name'], array());
          if ($checkboxes) foreach ($checkboxes as $checkIndex => $checkLabel) {
            $checked = in_array($checkIndex, $postCheckbox) ? ' checked="checked"' : null;
            $this->rend('<label class="checkbox ' .$deploy['class']. '">');
            $this->rend('<input type="checkbox" name="' .$nameCheckbox. '" value="' .$checkIndex. '" ' .$checked. ' /> '.$checkLabel);
            $this->rend('</label>');
          }
          break;

        case 'radio':
          foreach ($radio as $radioIndex => $radioLabel) {
            $checked = ($deploy['value'] == $radioIndex) ? ' checked="checked"' : null;
            $this->rend('<label class="radio ' .$deploy['class']. '">');
            $this->rend('<input ' .$this->paramRender($deploy, 'value').$checked. ' value="' .$radioIndex. '" /> '.$radioLabel);
            $this->rend('</label>');
          }
          break;

        case 'select':
          if(isset($deploy['multiple'])) $deploy['name'] .= '[]';
          if(!is_array(current($deploy['select']))) $deploy['select'] = array($deploy['select']);

          foreach ($deploy['select'] as $arrayLabel => $arrayEntries) {
            $arrayLabel = sizeof($deploy['select']) > 1 ? $deploy['name']. '_' .$arrayLabel : $deploy['name'];
            $arrayValue = a::get($deploy, 'value', a::get($this->values, $arrayLabel, r::post($arrayLabel)));

            $this->rend('<select name="' .$arrayLabel. '" ' .$this->paramRender($deploy, array('value', 'select', 'name')). '>', 'TAB');
            foreach ($arrayEntries as $index => $label) {
              if (is_array($label)) {
                $this->rend('<optgroup label="' .$index. '">');
                foreach($label as $optionIndex => $optionLabel) $this->option($optionIndex, $optionLabel, $arrayValue, $params);
                $this->rend('</optgroup>');
              } else $this->option($index, $label, $arrayValue, $params);
            }
            $this->rend('</select>', 'UNTAB');
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
          if($this->optionFormType != 'inline' and a::get($params, 'cancel', false))
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

  private function option($index, $label, $value = null, $params = null)
  {
    global $arrayValue;

    if(!$value) $value = $arrayValue;

    if(!isset($params['force_index']) and is_int($index) and $index == $label) $thisOption = null;
    else $thisOption = ' value="' .$index. '"';

    $selected = $value == $index ? ' selected="selected"' : null;
    $this->rend('<option' .$thisOption.$selected. '>'.$label. '</option>');
  }

  /*
  ########################################
  ############## RACCOURCIS ##############
  ########################################
  */

  // Add a field to the main form
  public function addField($name, $label, $type, $value, $additionalParams)
  {
    if(!is_array($additionalParams)) $additionalParams = str::parse('{' .$additionalParams. '}', 'json');
    $this->addElement(array('label' => $label, 'name' => $name, 'value' => $value, 'type' => $type, 'params' => $additionalParams));
  }

  //////////////////
  // CHAMPS TEXTE //
  //////////////////

  public function addNumber($name, $label = null, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label, 'number', $value, $additionalParams);
  }

  public function addText($name, $label = null, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label, 'text', $value, $additionalParams);
  }

  public function addPassword($name, $label = null, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label, 'password', $value, $additionalParams);
  }

  public function addTextarea($name, $label = null, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label, 'textarea', $value, $additionalParams);
  }

  public function addCheckbox($name, $label = null, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label, 'checkbox', $value, $additionalParams);
  }

  public function addHidden($name, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label = null, 'hidden', $value, $additionalParams);
  }

  public function addTel($name, $label = null, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label, 'tel', $value, $additionalParams);
  }

  public function addEmail($name, $label = null, $value = null, $additionalParams = null)
  {
    $this->addField($name, $label, 'email', $value, $additionalParams);
  }

  //////////////////
  ///// LISTES /////
  //////////////////

  public function addCheckboxes($name = null, $label = null, $checkboxes, $value = null, $additionalParams = null)
  {
    $additionalParams['checkboxes'] = $checkboxes;
    $this->addField($name, $label, 'checkboxes', null, $additionalParams);
  }
  public function addRadio($name = null, $label = null, $radio, $value = null, $additionalParams = null)
  {
    $additionalParams['radio'] = $radio;
    $this->addField($name, $label, 'radio', null, $additionalParams);
  }
  public function addSelect($name = null, $label = null, $select, $value = null, $additionalParams = null)
  {
    $additionalParams['select'] = $select;
    $this->addField($name, $label, 'select', $value, $additionalParams);
  }
  public function addDate($name = 'date', $label = null, $value = '0000-00-00', $additionalParams = null)
  {
    $value = explode('-', $value);
    $additionalParams['class'] = 'dateForm';
    $startingYear = a::get($additionalParams, 'start');
    $endingYear = a::get($additionalParams, 'end');

    $this->setValues(array($name.'_jour' => $value[2], $name.'_mois' => $value[1], $name.'_annee' => $value[0]));
    $this->addSelect($name, $label, $this->listeDate($startingYear, $endingYear), null, $additionalParams);
  }

  //////////////////
  /// FUNCTIONS ////
  //////////////////

  public function addType()
  {
    $formType = a::get($_GET, 'edit_' .$this->usable, 'add');
    $this->addHidden('edit', $formType);
  }
  public function addFile($name, $label = null, $additionalParams = null)
  {
    $this->render = str_replace('method="' ,'enctype="multipart/form-data" method="', $this->render);
    $this->addField($name, $label, 'file', null, $additionalParams);
  }
  public function addSubmit($name = 'Valider', $additionalParams = null)
  {
    if(!$additionalParams) $additionalParams['class'] = 'btn-cerberus';
    $this->addField($name, null, 'submit', null, $additionalParams);
  }

  //////////////////
  //// SELECTS /////
  //////////////////

  public function listeNumber($end, $start = 0, $step = 1)
  {
    return range($start, $end, $step);
  }

  // Champ date
  public function listeDate($startingYear = null, $endingYear = null)
  {
    if(!$startingYear) $startingYear = date('Y');
    if(!$endingYear) $endingYear = $startingYear + 10;

    return array(
      'jour' => $this->listeNumber(31, 1),
      'mois' => $this->listeNumber(12, 1),
      'annee' => $this->listeNumber($endingYear, $startingYear));
  }

  //////////////////////////////////////////////////////////////////
  //////////////////////// RENDER FUNCTIONS ////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
   * Insert raw text
   *
   * @param  string $text Content to add
   */
  public function insert($text)
  {
    $this->render .= $text;
  }

  /**
   * Add text to the render, with corresponding depth
   *
   * @param  string $content Content to add
   * @param  string $tabs    TAB/UNTAB
   */
  private function rend($content, $tabs = null)
  {
    if($tabs == 'UNTAB') $this->tabs--;

    if($this->tabs >= 0)
      $this->render .=
        str_repeat("\t", $this->tabs).
        $content.
        PHP_EOL;

    if($tabs == 'TAB') $this->tabs++;
  }

  /**
   * Add content and go one level deeper
   *
   * @param  string $content Content to add
   */
  private function tab($content)
  {
    $this->rend($content, 'TAB');
  }

  /**
   * Add content and go one level deeper
   *
   * @param  string $content Content to add
   */
  private function untab($content)
  {
    $this->rend($content, 'UNTAB');
  }

  private function paramRender($params, $except = null)
  {
    $render = null;
    if(!is_array($except)) $except = array($except);

    foreach($params as $key => $value)
      if((!empty($value) or $value === 0) and !in_array($key, $except)) $render .= $key.'="' .$value. '" ';

    return substr($render, 0, -1);
  }

  //////////////////////////////////////////////////////////////////
  ////////////////////////////// EXPORT ////////////////////////////
  //////////////////////////////////////////////////////////////////

  public function render()
  {
    echo $this->returns();
  }

  public function returns()
  {
    // Rendu du formulaire
    $this->rend('</form>', 'UNTAB');

    return $this->render;
  }
}
