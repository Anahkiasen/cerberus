<?php
use Cerberus\Objectable;

class ObjectableTest extends CerberusTest
{
  private $array = array('foo' => 'foo', 'bar' => 'bar');

  public $collection = array(
    array('foo1' => 'bar1', 'foo2' => 'bar2'),
    array('foo1' => 'kal1', 'foo2' => 'kal2'),
  );

  public function testCanCreateFromAnArray()
  {
    $object = Objectable::from($this->array);

    $this->assertEquals($this->array, $object->obtain());
  }

  public function testCanDynamicallySetValues()
  {
    $object = Objectable::from($this->array);
    $object->bis = 'ter';

    $array = $this->array;
    $array['bis'] = 'ter';

    $this->assertEquals($array, $object->obtain());
  }

  public function testCanDynamicallyGetValues()
  {
    $object = Objectable::from($this->array);

    $this->assertEquals('bar', $object->bar);
  }

  public function testCanSwapEntries()
  {
    $object = Objectable::from($this->array);
    $object = $object->swap('foo', 'bis', 'ter');

    $array = array('bis' => 'ter', 'bar' => 'bar');

    $this->assertEquals($array, $object->obtain());
  }

  public function testCanGetFirstValue()
  {
    $object = Objectable::from($this->array);

    $this->assertEquals('foo', $object->first());
  }

  public function testCanGetLastValue()
  {
    $object = Objectable::from($this->array);

    $this->assertEquals('bar', $object->last());
  }

  public function testCanCount()
  {
    $object = Objectable::from($this->array);

    $this->assertEquals(2, $object->count());
  }

  public function testCanBePrintedOut()
  {
    $object = Objectable::from($this->array)->__toString();

    $this->assertEquals('{"foo":"foo","bar":"bar"}', $object);
  }

  public function testCanDynamicallyUseArraysHelpers()
  {
    $object = Objectable::from($this->collection);
    $object = $object->pluck('foo2');

    $array = array('bar2', 'kal2');

    $this->assertEquals($array, $object->obtain());
  }
}