<?php
include 'dummy/Model.php';

use Cerberus\Models\Elegant;

class ElegantTest extends CerberusTest
{
  public function testCanPrintOutAModel()
  {
    $elegant = new Model(array('name' => 'foo'));
    $elegant = $elegant->__toString();

    $this->assertEquals('foo', $elegant);
  }

  public function testCanValidateAModel()
  {
    Model::$rules = array('name' => 'required');
    Input::replace(array('name' => null));

    $validation = Model::validate();
    $this->assertTrue($validation->fails());
  }
}