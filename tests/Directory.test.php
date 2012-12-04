<?php
use Cerberus\Directory;

// Dependencies
use Cerberus\File;

class DirectoryTest extends CerberusTest
{
  // Variables --------------------------------------------------- /

  private static $dummyFile = 'Dispatch.php';
  private static $dummyFolder = 'temp/';

  // Tests Setup ------------------------------------------------- /

  public function setUp()
  {
    Directory::create(self::$dummyFolder);
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

  public function testCanCreateASimpleDirectory()
  {
    $create = Directory::create(self::$dummyFolder);

    self::assertFileExists(self::$dummyFolder);
  }

  public function testCanCreateAWholePath()
  {
    $folder = self::$dummyFolder.'subTestFolder/subSubTestFolder/';
    $create = Directory::create($folder);

    self::assertFileExists($folder);
  }

  /**
   * @dataProvider paths
   */
  public function testCanGetTheLastFolderOfAPath($path = null, $expected = null)
  {
    $last = Directory::last($path);
    self::assertEquals($expected, $last);
  }

  /**
   * @dataProvider paths
   */
  public function testCanGetTheNthFolderFromAPath($path = null, $expectedLast = null, $nth = null, $expected = null)
  {
    $nth = Directory::nth($path, $nth);
    self::assertEquals($expected, $nth);
  }
}
