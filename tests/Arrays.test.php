<?php
require '_bootstrap.php';

use Cerberus\Arrays;

class ArraysTests extends CerberusTests
{
  public $arraySimple = array(
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3');

  public $array = array(
    array('foo1' => 'bar1', 'foo2' => 'bar2'),
    array('foo1' => 'kal1', 'foo2' => 'kal2'),
  );

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
    $array = $this->arraySimple;
    $return = Arrays::remove($array, 'key2');

    $this->assertEquals(array('key1' => 'value1', 'key3' => 'value3'), $return);
  }

  public function testRemoveKeyMultiple()
  {
    $array = $this->arraySimple;
    $return = Arrays::remove($array, array('key2', 'key3'));

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testRemoveValue()
  {
    $array = $this->arraySimple;
    $return = Arrays::removeValue($array, 'value2');

    $this->assertEquals(array('key1' => 'value1', 'key3' => 'value3'), $return);
  }

  public function testRemoveValueMultiple()
  {
    $array = $this->arraySimple;
    $return = Arrays::removeValue($array, array('value2', 'value3'));

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testPluck()
  {
    $array = $this->array;
    $return = Arrays::pluck($array, 'foo1');

    $this->assertEquals(array(0 => 'bar1', 1 => 'kal1'), $return);
  }

  public function testAverage()
  {
    $average1 = array(5, 10, 15, 20);
    $average2 = array('foo', 'b', 'ar');
    $average3 = array(array('lol'), 10, 20);

    $average1 = Arrays::average($average1);
    $average2 = Arrays::average($average2);
    $average3 = Arrays::average($average3);

    $this->assertEquals(13, $average1);
    $this->assertEquals(0,  $average2);
    $this->assertEquals(10, $average3);
  }

  public function testFirst()
  {
    $array = $this->arraySimple;
    $first = Arrays::first($array);
    $this->assertEquals('value1', $first);

    $array = $this->array;
    $first = Arrays::first($array);
    $this->assertEquals(array('foo1' => 'bar1', 'foo2' => 'bar2'), $first);
  }
}
