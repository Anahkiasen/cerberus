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
      'key2' => array('key21' => 'value21', 'key22' => 'value22')
      );

    $returnValue = Arrays::get($array, $key, $fallback);
    $this->assertEquals($shouldBe, $returnValue);
  }
}