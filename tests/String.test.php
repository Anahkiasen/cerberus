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

  public function provideAccord()
  {
    return array(
      array(10, '10 things'),
      array(1,  'one thing'),
      array(0,  'nothing'),
    );
  }

  public function provideFind()
  {
    return array(
      // Simple cases
      array(false, 'foo', 'bar'),
      array(true, 'foo', 'foo'),
      array(true, 'FOO', 'foo', false),
      array(false, 'FOO', 'foo', true),

      // Many needles, one haystack
      array(true, array('foo', 'bar'), self::$remove),
      array(false, array('vlu', 'bla'), self::$remove),
      array(true, array('foo', 'vlu'), self::$remove, false, false),
      array(false, array('foo', 'vlu'), self::$remove, false, true),

      // Many haystacks, one needle
      array(true, 'foo', array('foo', 'bar')),
      array(true, 'bar', array('foo', 'bar')),
      array(false, 'foo', array('bar', 'kal')),
      array(true, 'foo', array('foo', 'foo'), false, false),
      array(false, 'foo', array('foo', 'bar'), false, true),
    );
  }

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

  /**
   * @dataProvider provideAccord
   */
  public function testAccord($number, $expect)
  {
    $result = String::accord($number, $number. ' things', 'one thing', 'nothing');

    $this->assertEquals($expect, $result);
  }

  /**
   * @dataProvider provideFind
   */
  public function testFind($expect, $needle, $haystack, $caseSensitive = false, $absoluteFinding = false)
  {
    $result = String::find($needle, $haystack, $caseSensitive, $absoluteFinding);

    $this->assertEquals($expect, $result);
  }

  public function testNumberPad()
  {
    $result = String::numberPad(4);

    $this->assertEquals('04', $result);
  }

  public function testNumberPadAlreadyPadded()
  {
    $result = String::numberPad('004', 2);

    $this->assertEquals('04', $result);
  }
}