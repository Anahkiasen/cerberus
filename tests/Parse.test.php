<?php
use Cerberus\Parse;

class ParseTest extends CerberusTest
{
  public $array = array(
    array('foo1' => 'bar1', 'foo2' => 'bar2'),
    array('foo1' => 'kal1', 'foo2' => 'kal2'),
  );

  public function testCanCreateCsvFiles()
  {
    $csv = Parse::toCSV($this->array);
    $matcher = '"bar1";"bar2"' . PHP_EOL . '"kal1";"kal2"';

    $this->assertEquals($matcher, $csv);
  }

  public function testCanUseCustomCsvDelimiter()
  {
    $csv = Parse::toCSV($this->array, ',');
    $matcher = '"bar1","bar2"' . PHP_EOL . '"kal1","kal2"';

    $this->assertEquals($matcher, $csv);
  }

  public function testCanOutputCsvHeaders()
  {
    $csv = Parse::toCSV($this->array, ',', true);
    $matcher = 'foo1,foo2' . PHP_EOL . '"bar1","bar2"' . PHP_EOL . '"kal1","kal2"';

    $this->assertEquals($matcher, $csv);
  }

  public function testCanConvertToJson()
  {
    $json = Parse::toJSON($this->array);
    $matcher = '[{"foo1":"bar1","foo2":"bar2"},{"foo1":"kal1","foo2":"kal2"}]';

    $this->assertEquals($matcher, $json);
  }

  public function testCanParseJson()
  {
    $json = Parse::toJSON($this->array);
    $array = Parse::fromJSON($json);

    $this->assertEquals($this->array, $array);
  }
}
