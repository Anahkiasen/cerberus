<?php
use Cerberus\Toolkit\Cookie;

class CookieTest extends PHPUnit_Framework_TestCase
{
	public function testSetCookie()
	{
		cookie::set('testCookie', 'testValue');

		self::assertArrayHasKey('testCookie', $_COOKIE);
		self::assertEquals($_COOKIE['testCookie'], 'testValue');

		cookie::remove('testCookie');
	}

	public function testGetCookie()
	{
		cookie::set('testCookie2', 'testValue');

		$result = cookie::get('testCookie2');
		self::assertEquals($result, 'testValue');

		cookie::remove('testCookie2');
	}

	public function testRemoveCookie()
	{
		cookie::set('testCookie3', 'testValue');

		cookie::remove('testCookie3');
		self::assertArrayNotHasKey('testCookie3', $_COOKIE);
	}
}
