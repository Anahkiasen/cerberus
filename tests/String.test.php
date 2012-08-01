<?php
use Cerberus\Toolkit\String;

class StringTests extends CerberusTests
{
  public static $remove = 'foo foo bar foo kal ter son';

  // Data providers ------------------------------------------------ /

  public function provideStartsWith()
  {
    return array(
      array('foobar', 'foo', true),
      array('foobar', 'bar', false)
    );
  }

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

  /**
   * @dataProvider provideStartsWith
   */
  public function testStartsWith($haystack, $needle, $expect)
  {
    $result = String::startsWith($haystack, $needle);

    $this->assertEquals($expect, $result);
  }

  public function testToggleMatch()
  {
    $firstToggle = String::toggle('foo', 'foo', 'bar');
    $this->assertEquals('bar', $firstToggle);
  }

  public function testToggleUnmatchStrict()
  {
    $firstToggle = String::toggle('dei', 'foo', 'bar');
    $this->assertEquals('dei', $firstToggle);
  }

  public function testToggleUnmatchLoose()
  {
    $firstToggle = String::toggle('dei', 'foo', 'bar', $loose = true);
    $this->assertEquals('foo', $firstToggle);
  }

}