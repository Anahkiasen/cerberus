<?php
class ServerTest extends PHPUnit_Framework_TestCase
{
	// Unit Setup -------------------------------------------------- /

	public function setUp()
	{
		$_SERVER['HTTP_HOST'] = '127.0.0.1';
		$_SERVER['TESTKEY'] = 'testValue';
	}

	public function tearDown()
	{
		unset($_SERVER['TESTKEY']);
	}

	// Tests ------------------------------------------------------- /

	public function testGetAll()
	{
		$server = server::get();

		self::assertArrayHasKey('TESTKEY', $server);
		self::assertEquals('testValue', $server['TESTKEY']);
	}

	public function testGet()
	{
		$server = server::get('TESTKEY');

		self::assertEquals('testValue', $server);
	}

	public function testHost()
	{
		$host = server::host();

		self::assertEquals('127.0.0.1', $host);
	}

	public function testLocal()
	{
		$local = server::local();

		self::assertTrue($local);
	}

	public function testIp()
	{
		$ip = server::ip();

		self::assertEquals(null, $ip);
	}

	public function testLocation()
	{
		$ip = '88.189.108.137';
		$location = server::location($ip);

		self::assertEquals(
			array(
				'statusCode' => 'OK',
				'statusMessage' => '',
				'ipAddress' => $ip,
				'countryCode' => 'FR',
				'countryName' => 'FRANCE'),
			$location);
	}
}
