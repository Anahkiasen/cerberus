<?php
require 'startTests.php';

use Cerberus\Arrays;

class ArraysTests extends CerberusTests
{

  // Mock data ----------------------------------------------------- /

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
  public function testCanGetValueFromArray($key, $fallback, $shouldBe)
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

  public function testCanSetValueInArray()
  {
    $array = array('foo' => 'foo', 'bar' => 'bar');
    $array = Arrays::set($array, 'kal.ter', 'foo');
    $matcher = array('foo' => 'foo', 'bar' => 'bar', 'kal' => array('ter' => 'foo'));

    $this->assertEquals($matcher, $array);
  }

  public function testCanRemoveKeyFromArray()
  {
    $array = $this->arraySimple;
    $return = Arrays::remove($array, 'key2');

    $this->assertEquals(array('key1' => 'value1', 'key3' => 'value3'), $return);
  }

  public function testCanRemoveMultipleKeysFromArray()
  {
    $array = $this->arraySimple;
    $return = Arrays::remove($array, array('key2', 'key3'));

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testCanRemoveValueFromArray()
  {
    $array = $this->arraySimple;
    $return = Arrays::removeValue($array, 'value2');

    $this->assertEquals(array('key1' => 'value1', 'key3' => 'value3'), $return);
  }

  public function testCanRemoveMultipleValuesFromArray()
  {
    $array = $this->arraySimple;
    $return = Arrays::removeValue($array, array('value2', 'value3'));

    $this->assertEquals(array('key1' => 'value1'), $return);
  }

  public function testCanPluckColumns()
  {
    $array = $this->array;
    $return = Arrays::pluck($array, 'foo1');

    $this->assertEquals(array(0 => 'bar1', 1 => 'kal1'), $return);
  }

  public function testCanCalculateAverageValue()
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

  public function testCanGetRandomValue()
  {
    $array = array(5, 10, 15, 20);
    $random = Arrays::random($array);

    $this->assertContains($random, $array);
  }

  public function testCanGetFirstValue()
  {
    $array = $this->arraySimple;
    $first = Arrays::first($array);
    $this->assertEquals('value1', $first);

    $array = $this->array;
    $first = Arrays::first($array);
    $this->assertEquals(array('foo1' => 'bar1', 'foo2' => 'bar2'), $first);
  }

  public function testCanFlattenArraysToDotNotation()
  {
    $array = array(
      'foo' => 'bar',
      'kal' => array(
        'foo' => array(
          'bar', 'ter',
        ),
      ),
    );
    $flattened = array(
      'foo' => 'bar',
      'kal.foo.0' => 'bar',
      'kal.foo.1' => 'ter',
    );

    $flatten = Arrays::flatten($array);

    $this->assertEquals($flatten, $flattened);
  }
}
