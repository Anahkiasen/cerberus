<?php
class validTest extends PHPUnit_Framework_TestCase
{
	public function passwords()
	{
		return array(
			array('pass'),
			array('password'),
			array('password513'),
			array('98')
			);
	}

	/**
	 * @data Provider passwords
	 */
	public function testPassword($password = null)
	{
		$valid = v::password($password);
		if(strlen($password) < 4) self::assertFalse($valid);
		else self::assertTrue($valid);
	}
}