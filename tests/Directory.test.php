<?php
use Cerberus\Toolkit\Directory;

// Dependencies
use Cerberus\Toolkit\File;

class DirectoryTest extends PHPUnit_Framework_TestCase
{
  // Variables --------------------------------------------------- /

  private static $dummyFile = 'Dispatch.php';
  private static $dummyFolder = 'temp/';

  // Tests Setup ------------------------------------------------- /

  public function setUp()
  {
    Directory::make(self::$dummyFolder);
  }

  public static function tearDownAfterClass()
  {
    Directory::remove(self::$dummyFolder);
  }

  public function tearDown()
  {
    Directory::remove(self::$dummyFolder);
  }

  // Data providers  --------------------------------------------- /

  public function paths()
  {
    return array(
      array('this/is/a/path/file.php', 'path', 1, 'a'),
      array('this/is/a/path', 'path', 2, 'is'),
      array('cerberus/file.php', 'cerberus', 0, 'cerberus'),
      array('cerberus', 'cerberus', 0, 'cerberus'),
      array('this/is/a/path/', 'path', 2, 'is')
      );
  }

  // Tests ------------------------------------------------------- /

  public function testMakeSimple()
  {
    $folder = self::$dummyFolder.'testFolder';
    $create = directory::make($folder);

    self::assertFileExists($folder);
  }

  public function testMakeComplex()
  {
    $folder = self::$dummyFolder.'subTestFolder/subSubTestFolder/';
    $create = directory::make($folder);

    self::assertFileExists($folder);
  }

  /**
   * @dataProvider paths
   */
  public function testLast($path = null, $expected = null)
  {
    $last = Directory::last($path);
    self::assertEquals($expected, $last);
  }

  /**
   * @dataProvider paths
   */
  public function testNth($path = null, $expectedLast = null, $nth = null, $expected = null)
  {
    $nth = Directory::nth($path, $nth);
    self::assertEquals($expected, $nth);
  }
}