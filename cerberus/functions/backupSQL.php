<?php
/*
  Fonction backupSQL
  # Effectue une sauvegarde de la base de donnée
  # Créer un dossier par date dans le dossier PATH_CACHEsql par défaut
  # Ne garde que les sauvegardes du mois en cours et celles du mois précédent
  # aux dates du 1er et du 15

  $filename
    Identifiant du fichier de sauvegarde - usuellement le nom de la base de données
*/
function backupSQL()
{
  // Sauvegarde et chargement de la base
  $tablesBases = db::showtables();
  if (empty($tablesBases)) {
    // Si la base de données est vide, chargement de dernière la sauvegarde
    foreach(glob(PATH_CACHE. 'sql/*') as $file)
      $fichier = $file;

    if (isset($fichier)) {
      $fichier = a::get(explode('/', $fichier), 3);

      foreach(glob(PATH_CACHE. 'sql/' .$fichier. '/*.sql') as $file)
        $fichier = $file;

      multiQuery(file_get_contents($fichier), array(config::get('db.host'), config::get('db.user'), config::get('db.mdp'), config::get('db.name')));
    }
  } elseif (!empty($tablesBases)) {
    $filename = str::slugify(config::get('sitename', config::get('db.name')));

    // Définition du nom du dossier
    $path = PATH_CACHE. 'sql/';
    $folderName = $path.date('Y-m-d');

    // Création du dossier à la date si inexistant
    if (!file_exists($folderName) and !empty($tablesBases)) {
      $tablesBases = array_values($tablesBases);

      // Suppression des sauvegardes inutiles
      foreach (glob($path. '*') as $file) {
        if (is_dir($file)) {
          $folderDate = explode('-', str_replace($path, '', $file));

          if($folderDate[0] != date('Y')) $unlink = true;
          elseif($folderDate[0] == date('Y') and (date('m') - $folderDate[1] > 1)) $unlink = true;
          elseif($folderDate[0] == date('Y') and (date('m') - $folderDate[1] == 1) and !in_array($folderDate[2], array(1, 15))) $unlink = true;

          if (isset($unlink)) {
            f::remove($file);
            //echo 'La sauvegarde du ' .implode('-', $folderDate). ' a bien été supprimée<br />';
          }
        }
      }

      // Récupération de la liste des tables
      $file = NULL;
      foreach ($tablesBases as $table) {
        $file .= "DROP TABLE IF EXISTS $table;\n";

        // Création de la table
        $tableCreate = mysql_fetch_array(mysql_query("SHOW CREATE TABLE $table"));
        $file .= $tableCreate[1].";\n";

        // Contenu de la table
        $tableContent = mysql_query("SELECT * FROM $table");
        while ($row = mysql_fetch_assoc($tableContent)) {
          $lineInsert = "INSERT INTO $table (";
          $lineValue = ") VALUES (";

          // Valeurs
          foreach ($row as $field => $value) {
            $lineInsert .= "`$field`, ";
            $lineValue .= "'" .mysql_real_escape_string($value). "', ";
          }

          // Suppression du , en trop
          $lineInsert = substr($lineInsert, 0, -2);
          $lineValue = substr($lineValue, 0, -2);
          $file .= $lineInsert.$lineValue. ");\n";
        }
      }

      // Création du fichier
      $filename = $filename. '-' .date('H-i-s'). '.sql';
      f::write($folderName. '/' .$filename, $file);

      return
      'Le fichier ' .$filename. ' a bien été crée<br />
      Tables : ' .implode(', ', $tablesBases);
    } else return 'Une sauvegarde existe déjà pour cette date.';
  }
}
