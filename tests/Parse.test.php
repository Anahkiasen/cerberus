<?php
use Cerberus\Parse;

class ParseTest extends CerberusTest
{
  public $array = array(
    array('foo1' => 'bar1', 'foo2' => 'bar2'),
    array('foo1' => 'kal1', 'foo2' => 'kal2'),
  );

  public function testCsv()
  {
    $array = $this->array;
    $csv = Parse::toCSV($array);

    $this->assertEquals('"bar1";"bar2"' . PHP_EOL . '"kal1";"kal2"', $csv);
  }

  public function testCsvDelimiter()
  {
    $array = $this->array;
    $csv = Parse::toCSV($array, ',');

    $this->assertEquals('"bar1","bar2"' . PHP_EOL . '"kal1","kal2"', $csv);
  }

  public function testCsvHeaders()
  {
    $array = $this->array;
    $csv = Parse::toCSV($array, ',', true);

    $this->assertEquals('foo1,foo2' . PHP_EOL . '"bar1","bar2"' . PHP_EOL . '"kal1","kal2"', $csv);
  }
}
