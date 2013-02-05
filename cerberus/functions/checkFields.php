<?php
/*
  Fonction checkFields
  # Vérifie si un formulaire a été correctement rempli

  $fields
    Liste des champs obligatoires
    Peut préciser le type d'un champ pour qu'il
    soit reconnu sous la syntaxe CHAMP => TYPE
*/
use Cerberus\Toolkit\Valid;
use Cerberus\Toolkit\Language as l;
use Cerberus\Toolkit\String as str;

function checkFields()
{
  $mailbody = NULL;

  // Liste des champs voulus et incomplets
  $funcGet = func_get_args();
  foreach ($funcGet as $id => $champ) {
    if(is_array($champ)) $fields[key($champ)] = $champ[key($champ)];
    else $fields[$champ] = $champ;
  }
  $unfilled = array_keys($fields);
  $misfilled = array();
  $erreurs = array();

  // Lecture des données
  foreach ($fields as $key => $type) {
    $POST = $_POST[$key];
    if (!empty($POST) or $type == 'facultative') {
      $unfilled = array_diff($unfilled, array($key));
      if (valid::check($POST, $type)) {
        $mailbody .= (MULTILANGUE)
          ? '<strong>' .l::get('form-' .$key, ucfirst($key)). '</strong> : '
          : '<strong>' .ucfirst($key). '</strong> : ';
        $mailbody .= stripslashes($POST). '<br />';
      } else $misfilled[] = $key;
    }
  }

  // On vérifie que les champs sont remplis
  $isUnfilled = l::get('form-erreur-incomplete', 'Un ou plusieurs champs sont incomplets');
  $isMisfilled = l::get('form-erreur-incorrect', 'Un ou plusieurs champs sont incorrects');

  $typesErreur = array('un', 'mis');
  foreach ($typesErreur as $erreur) {
    $variable = ${$erreur. 'filled'};
    if (isset($variable) and !empty($variable)) {
      if(MULTILANGUE) foreach($variable as $key => $value) $variable[$key] = l::get('form-' .$value, ucfirst($value));
      else foreach($variable as $key => $value) $variable[$key] = ucfirst($value);
      $newError = ${'is' .ucfirst($erreur). 'filled'}. ' :';
      $newError .= (count($variable) > 3) ? '<br />' : ' ';
      $newError .= implode(', ', $variable);

      $erreurs[] = $newError;
      $newError = NULL;
    }
  }

  // Affiche des possibles erreurs, sinon validation
  if (!empty($erreurs)) {
    str::display(implode('<br />', $erreurs), 'error');

    return false;
  } else return (MULTILANGUE) ? $mailbody : true;
}
