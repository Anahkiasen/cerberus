<?php
abstract class CerberusTests extends PHPUnit_Framework_TestCase
{
  // Wrappers ------------------------------------------------------ /

  /**
   * Starts the bundle
   */
  public static function setUpBeforeClass()
  {
    Bundle::start('cerberus');
    URL::$base = 'http://test';
    Config::set('application.languages', array('fr', 'en'));
    Config::set('application.index', '');
    Config::set('application.language', 'en');
  }
}
