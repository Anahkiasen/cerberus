<?php
use Cerberus\Toolkit\Session;

class SessionTest extends PHPUnit_Framework_TestCase
{
	// Destroy any existing session everytime a function ends

	public function setUp()
	{
		if(session_id() != '') session_destroy();
	}

	// Tests

	public function testStart()
	{
		$beforeSession = session_id();

		session::start();
		$activeSession = session_id();

		self::assertEmpty($beforeSession);
		self::assertNotEmpty($activeSession);

	}

	public function testDestroy()
	{
		$beforeSession = session_id();

		session_start();
		session::destroy();

		$afterDestroy = session_id();

		self::assertEmpty($beforeSession);
		self::assertEmpty($afterDestroy);
	}

	public function testRestart()
	{
		$_SESSION['testRestart'] = true;
		self::assertArrayHasKey('testRestart', $_SESSION);

		session::restart();
		self::assertArrayNotHasKey('testRestart', $_SESSION);
	}

	public function testSessionId()
	{
		session_start();

		self::assertSame(session_id(), session::id());
	}

	public function testSet()
	{
		session_start();

		self::assertEmpty($_SESSION);
		session::set('testKey', 'testValue');
		self::assertArrayHasKey('testKey', $_SESSION);
		self::assertContains('testValue', $_SESSION);
	}

	public function testGetSessionAll()
	{
		session_start();

		$match = array('testKey' => 'testValue', 'testKey2' => 'testValue2');
		$_SESSION = $match;

		$get = session::get();

		self::assertEquals($match, $get);
	}

	public function testGetSession()
	{
		session_start();

		$match = array('testKey' => 'testValue', 'testKey2' => 'testValue2');
		$_SESSION = $match;

		$get = session::get('testKey');

		self::assertEquals('testValue', $get);
	}

	public function testExists()
	{
		$before = session::exists();

		session_start();

		$after = session::exists();

		self::assertFalse($before);
		self::assertTrue($after);
	}

	public function testRemove()
	{
		session_start();

		$_SESSION['testKey'] = 'testValue';

		session::remove('testKey');
		self::assertArrayNotHasKey('testKey', $_SESSION);
		self::assertNotContains('testValue', $_SESSION);
	}
}
