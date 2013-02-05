<?php
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
namespace Cerberus\Modules;

use Cerberus\Toolkit\Language as l;

class SelectDeprecated extends FormDeprecated
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
    if ($overwrite) {
      foreach($liste as $key => $value)
        if(!is_array($value)) $thisListe[$value] = $value;
        else {
          unset($newArray);
          foreach($value as $skey => $svalue) $newArray[$svalue] = $svalue;
          $thisListe[$key] = $newArray;
        }
    } else $thisListe = $liste;
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
    if ($stateField) {
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
    if (is_array($lol[0])) {
      foreach($this->liste as $key => $value)
        $this->createSelect($key, $value);
    } else $this->createSelect($this->name, $this->liste);

    if($stateField) $this->render .= '</dd></dl>';
  }
    // Création d'un <select> (sous-fonction de createElement)
    public function createSelect($name, $liste)
    {
      $thisValue = ($this->value === "")
        ? $this->defineValue($name)
        : $this->value;

      $this->render .= '<select name="' .$name. '" ';
      foreach($this->params as $key => $value) $this->render .= $key. '="' .$value. '"';
      $this->render .= '>';

      foreach ($liste as $key => $value) {
        if($key == $value and $thisValue == $value) $selected = 'selected="selected"';
        elseif($key != $value and $thisValue == $key) $selected = 'selected="selected"';
        else $selected = null;

        if($key === $value) $this->render .= '<option ' .$selected. '>' .$value. '</option>';
        else $this->render .= '<option value="' .$key. '" ' .$selected. '>' .$value. '</option>';
      }

      $this->render .= '</select>';
    }
}
