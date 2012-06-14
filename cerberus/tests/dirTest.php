<?php
class DirTest extends PHPUnit_Framework_TestCase
{
	private static $dummyFile = 'core.dispatch.php';
	private static $dummyFolder = 'temp';

	public static function setUpBeforeClass()
	{
		self::$dummyFolder .= DIRECTORY_SEPARATOR;
	}

	public function testMakeSimple()
	{
		$folder = self::$dummyFolder.'testFolder';
		$create = dir::make($folder);

		self::assertFileExists($folder);
		@rmdir($folder.'/');
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
	 * @depends testMaddkeSimple
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
		var_dump($dir2);
		self::assertContains($folder1, $dir2['children']);
	}

	public function testMakeComplex()
	{
		$folder = self::$dummyFolder.'subTestFolder/subSubTestFolder/';
		$create = dir::make($folder);

		self::assertFileExists($folder);
		@rmdir('testFolder');
	}
}