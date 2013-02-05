<?php
/**
 * Loads a class if called
 *
 * @param string    $className The class to load
 */
function classLoader($className)
{
  // Add subfolder classes/ and switcher to / delimitor
  $className = str_replace('Cerberus', 'cerberus/classes', $className);
  $className = str_replace('\\', '/', $className);

  // Set file name
  $file = $className. '.php';

  // If we found correspond classes and they're not already loaded
  if($file and file_exists($file) and !class_exists($className))
    require_once $file;
}

// Register classLoader
spl_autoload_register('classLoader');
