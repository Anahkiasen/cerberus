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
    $array = explode("\r", $data);
    if (count($array == 1)) $array = explode("\n", $data);

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

    // Don't convert if it's not an array
    if(!is_array($data)) return $data;

    // Fetch headers if requested
    if ($exportHeaders) {
      $headers = array_keys(Arrays::first($data));
      $csv[] = implode($delimiter, $headers);
    }

    // Quote values and create row
    foreach ($data as $header => $row) {

      // If single column
      if (!is_array($row)) {
        $csv[] = '"'.$header.'"'.$delimiter.'"'.$row.'"';
        continue;
      }

      // Else add values
      foreach($row as $key => $value) {
        $row[$key] = '"' .stripslashes($value). '"';
      }

      $csv[] = implode($delimiter, $row);
    }

    return implode(PHP_EOL, $csv);
  }
}
