<?php
abstract class CerberusTests extends PHPUnit_Framework_TestCase
{
  // Wrappers ------------------------------------------------------ /

  /**
   * Starts the bundle
   */
  public function setUp()
  {
    Bundle::start('cerberus');
  }
}
