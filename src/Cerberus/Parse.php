<?php
/**
 *
 * Parse
 *
 * Parse from various formats to various formats
 */
namespace Cerberus;

class Parse
{

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// FROM ////////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Converts data from JSON
   *
   * @param string $data The data to parse
   *
   * @return mixed
   */
  public static function fromJSON($data)
  {
    return json_decode($data, true);
  }

  /**
   * Converts data from CSV
   *
   * @param string $data The data to parse
   *
   * @return mixed
   */
  public static function fromCSV($data)
  {
    // Explodes rows
    $array = explode("\r", $string);
    if (count($array == 1)) $array = explode("\n", $string);

    // Parse the columns in each row
    foreach ($array as $row => $rawColumns) {

      // Prepare for the various separators
      $columns = explode("\t", $rawColumns);
      if(sizeof($columns) == 1) $columns = explode(';', $rawColumns);

      $array[$row] = $columns;
    }

    return $array;
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////////// TO ////////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Converts data to JSON
   *
   * @param string $data The data to convert
   *
   * @return string Converted data
   */
  public static function toJSON($data)
  {
    return json_encode($data);
  }

  /**
   * Converts data to CSV
   *
   * @param mixed $data The data to convert
   *
   * @return string Converted data
   */
  public static function toCSV($data, $delimiter = ';', $exportHeaders = false)
  {
    // Convert objects to arrays
    if(is_object($data)) $data = (array) $data;

    // Convert arrays
    if(is_string($data)) return $data;
    elseif (is_array($data)) {
      $csv = null;

      // Fetch headers if requested
      if ($exportHeaders) {
        $headers = array_keys(Arrays::first($data));
        $csv .= implode($delimiter, $headers);
      }

      foreach ($data as $header => $row) {
        // Add line break if we're not on the first row
        if(!empty($csv)) $csv .= PHP_EOL;

        // Quote values and create row
        if (is_array($row)) {
          foreach($row as $key => $value)
            $row[$key] = '"' .stripslashes($value). '"';
            $csv .= implode($delimiter, $row);
        } else $csv .= $header.$delimiter.$row;
      }

      return $csv;
    }
  }
}
