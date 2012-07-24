<?php
use Cerberus\Toolkit\String;

class StringTests extends CerberusTests
{
  public static $remove = 'foo foo bar foo kal ter son';

  // Data providers ------------------------------------------------ /

  // Wrappers ------------------------------------------------------ /

  // Tests --------------------------------------------------------- /

  public function testRemove()
  {
    $return = String::remove('bar', self::$remove);

    $this->assertEquals('foo foo  foo kal ter son', $return);
  }

  public function testRemoveMultiple()
  {
    $return = String::remove(array('foo', 'son'), self::$remove);

    $this->assertEquals('bar  kal ter', $return);
  }
}