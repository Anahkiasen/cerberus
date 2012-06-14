<?php
class DirTest extends PHPUnit_Framework_TestCase
{
	private static $dummyFile = 'core.dispatch.php';
	private static $dummyFolder = 'temp/';

	public static function tearDownAfterClass()
	{
		dir::remove(self::$dummyFolder);
	}

	public function paths()
	{
		return array(
			array('this/is/a/path/file.php', 'path'),
			array('this/is/a/path', 'path'),
			array('cerberus/file.php', 'cerberus'),
			array('cerberus', 'cerberus'),
			);
	}

	public function testMakeSimple()
	{
		$folder = self::$dummyFolder.'testFolder';
		$create = dir::make($folder);

		self::assertFileExists($folder);
	}

	public function testRead()
	{
		$read = dir::read('cerberus/class');

		self::assertInternalType('array', $read);
		self::assertContains(self::$dummyFile, $read);
		self::assertNotContains('.DS_Store', $read);
	}

	public function testInspect()
	{
		$folder = 'cerberus/class';

		$inspect = dir::inspect($folder);
		extract($inspect);

		self::assertEquals('class', $name);
		self::assertEquals($folder, $root);
		self::assertEquals(filemtime($folder), $modified);
		self::assertContains('plugins', $children);
		self::assertContains(self::$dummyFile, $files);
	}

	public function testRename()
	{
		$folder1 = self::$dummyFolder.'rename1';
		$folder2 = self::$dummyFolder.'rename2';

		// Create folder and rename it
		dir::make($folder1);
		$rename = dir::rename($folder1, $folder2);

		// Check if the old one is not there but the new one is
		self::assertTrue($rename);
		self::assertFileExists($folder2);
		self::assertFileNotExists($folder1);
	}

	/**
	 * @depends testMakeSimple
	 */
	public function testMove()
	{
		$folder1 = self::$dummyFolder.'move1';
		$folder2 = self::$dummyFolder.'move2';

		// Create two basic folders
		dir::make($folder1);
		dir::make($folder2);

		// Move one into the other
		$move = dir::move($folder1, $folder2);

		// Check if the folder moved
		$dir2 = dir::inspect($folder2);
		self::assertArrayHasKey('children', $dir2);
		self::assertContains('move1', $dir2['children']);
	}

	public function testRemove()
	{
		$folder = self::$dummyFolder.'remove1';

		$make = dir::make($folder);
		self::assertTrue($make);
		self::assertFileExists($folder);

		$remove = dir::remove($folder);
		self::assertTrue($remove);
		self::assertFileNotExists($folder);
	}

	public function testEmpty()
	{
		$folder = self::$dummyFolder.'remove2';
		$file = $folder.'/test.txt';

		$make = dir::make($folder);
		self::assertTrue($make);
		self::assertFileExists($folder);

		$create = f::create($file);
		self::assertTrue($create);
		self::assertFileExists($file);

		dir::clean($folder);
		self::assertFileExists($folder);
		self::assertFileNotExists($file);
	}

	/**
	 * @dataProvider paths
	 */
	public function testLast($path = null, $expected = null)
	{
		$last = dir::last($path);
		self::assertEquals($expected, $last);
	}

	public function testMakeComplex()
	{
		$folder = self::$dummyFolder.'subTestFolder/subSubTestFolder/';
		$create = dir::make($folder);

		self::assertFileExists($folder);
	}
}