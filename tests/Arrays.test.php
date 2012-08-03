<?php
require '_bootstrap.php';

use Cerberus\Toolkit\Arrays;

class ArraysTests extends CerberusTests
{
  // Data providers ------------------------------------------------ /

  public function provideGet()
  {
    return array(
      array('key1', null, 'value1'),
      array('key2', null, 'key2' => array('key21' => 'value21', 'key22' => 'value22')),
      array('key2.key21', null, 'value21'),
      array('key3', null, null),
      array('key3', 'fallback', 'fallback')
    );
  }

  // Tests --------------------------------------------------------- /

  /**
   * Test the get function
   * @dataProvider provideGet
   */
  public function testGet($key, $fallback, $shouldBe)
  {
    $array = array(
      'key1' => 'value1',
      'key2' => array(
        'key21' => 'value21',
        'key22' => 'value22')
      );
    $returnValue = Arrays::get($array, $key, $fallback);

    $this->assertEquals($shouldBe, $returnValue);
  }

  public function testRemoveKey()
  {
    $array = array(
      'key1' => 'value1',
      'key2' => 'value2');
    $return = Arrays::remove($array, 'key2');

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testRemoveKeyMultiple()
  {
    $array = array(
      'key1' => 'value1',
      'key2' => 'value2',
      'key3' => 'value3');
    $return = Arrays::remove($array, array('key2', 'key3'));

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testRemoveValue()
  {
    $array = array(
      'key1' => 'value1',
      'key2' => 'value2',
      'key3' => 'value2');
    $return = Arrays::removeValue($array, 'value2');

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testRemoveValueMultiple()
  {
    $array = array(
      'key1' => 'value1',
      'key2' => 'value2',
      'key3' => 'value3');
    $return = Arrays::removeValue($array, array('value2', 'value3'));

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testPluck()
  {
    $array = array(
     'key1' => array('subkey1' => 'subvalue1', 'subkey2' => 'subvalue2'),
     'key2' => array('subkey1' => 'subvalue1', 'subkey2' => 'subvalue2')
    );
    $return = Arrays::pluck($array, 'subkey1');

    $this->assertEquals(array('key1' => 'subvalue1', 'key2' => 'subvalue1'), $return);
  }
}