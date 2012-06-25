<?php
class ConfigTest extends PHPUnit_Framework_TestCase
{
	// Setup and Teardown ------------------------------------------ /

	private static $file = 'cerberus/tests/test.json';

	public static function setUpBeforeClass()
	{
		new config(self::$file);
	}

	public static function tearDownAfterClass()
	{
		f::remove(self::$file);
	}

	public function tearDown()
	{
		new config(self::$file);
	}

	// Tests ------------------------------------------------------- /

	public function testSetGet()
	{
		config::set('testKey', 'testValue');

		$return = config::get('testKey');
		self::assertEquals($return, 'testValue');
	}

	public function testSetArray()
	{
		// Custom values
		$array = array(
			'testKey1' => 'testValue1',
			'testKey2' => 'testValue2',
			'index' => 'testValue3');

		// Set array and get config
		config::set($array);
		$config = config::get();

		// Check each entry
		foreach($array as $k => $v)
		{
			self::assertArrayHasKey($k, $config);
			self::assertEquals($config[$k], $v);
		}
	}

	public function testDefaults()
	{
		$get = config::get('db.charset');

		self::assertEquals($get, 'utf8');
	}

	public function testGetAll()
	{
		$all = config::get();

		self::assertInternalType('array', $all);
		self::assertNotEmpty($all);
		self::assertArrayHasKey('db.charset', $all);
	}

	public function testErase()
	{
		$newValue = 'testValue';

		$before = config::get('db.charset');
		config::set('db.charset', $newValue);
		$after = config::get('db.charset');

		self::assertEquals($newValue, $after);
		self::assertNotEquals($before, $after);
	}

	public function testLoad()
	{
		// Writing fake config file
		$json = array('testKey' => 'testValue');
		f::write('temp.json', json_encode($json));

		// Loading it and checking config value
		config::load('temp.json');
		$return = config::get('testKey');

		// Assertions
		self::assertEquals($return, 'testValue');

		// Remove temp file
		f::remove('temp.json');
	}

	public function testChangeLoad()
	{
		$tempFile = 'test.json';
		f::write($tempFile, '{"testLoad":"true"}');

		$change = config::change($tempFile);
		self::assertFileExists($tempFile);
		self::assertArrayHasKey('testLoad', config::get());
		self::assertEquals('true', config::get('testLoad'));

		f::remove($tempFile);
	}

	public function testChangeCreate()
	{
		$tempFile = 'test.json';

		$change = config::change($tempFile);
		self::assertFileExists($tempFile);

		f::remove($tempFile);
	}

	public function testLoadErase()
	{
		$before = config::get('index');

		// Writing fake config file
		$json = array('index' => 'testValue');
		f::write('temp.json', json_encode($json));

		// Loading it and checking config value
		config::load('temp.json');
		$return = config::get('index');

		// Assertions
		self::assertEquals($return, 'testValue');

		// Remove temp file
		f::remove('temp.json');
	}

	public function testMysql()
	{
		$login = array(
			'local_name'  => 'localname',
			'db_host'     => 'dbhost',
			'db_user'     => 'dbuser',
			'db_password' => 'dbpassword',
			'db_name'     => 'dbname');
		extract($login);

		// Saving values
		config::mysql($local_name, $db_host, $db_user, $db_password, $db_name);
		$config = config::get();

		foreach($login as $k => $v)
		{
			$k = str_replace('_', '.', $k);
			self::assertArrayHasKey($k, $config);
			self::assertEquals($config[$k], $v);
		}
	}
}
