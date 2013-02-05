<?php
use Cerberus\Toolkit\Valid;

class ValidTest extends PHPUnit_Framework_TestCase
{
	// dataProviders ---------------------------------------------- /

	public function passwords()
	{
		return array(
			array('pass', 'pass'),
			array('password', 'password'),
			array('123456', '987654')
			);
	}

	public function dates()
	{
		return array(
			array('2012-01-03',             true),
			array('1981-13-50',             false),
			array('12/17/17',               true),
			array('12-17-17',               false),
			array('December the 3rd, 1984', false),
			array('3 December 1984',        true)
			);
	}

	public function emails()
	{
		return array(
			array('simpleString',      false),
			array('123456',            false),
			array('mail@mail',         false),
			array('mail@mail.fr',      true),
			array('mail.mail@mail.fr', true),
			array('mail@mail.x',       false)
			);
	}

	public function urls()
	{
		return array(
			array('simpleString',                false),
			array('http://www.stappler.fr/',     true),
			array('www.stappler.fr',             true),
			array('http://stappler.fr/',         true),
			array('www.stappler.fr/admin/page/', true),
			array('stappler.fr/index.php?page=url1234-test', true),
			array('www.scope-creep.eu',          true),
			array('www.scrope_creep.eu',         true),
			array('www.123456789.eu',            true),
			array('sdfsdfsdfs',                  false),
			array('www.sdfsfsdfsdfs',            true),
			array('www.thrthrghf.98789879',      true)
			);
	}

	public function filenames()
	{
		return array(
			array('t',          false),
			array('test',       true),
			array('test.txt',   true),
			array('12345.csv',  true),
			array('@@@_$$.txt', false),
			array('11pkp##k',   false),
			array("L'oiseau",   false),
			);
	}

	public function checks()
	{
		// TODO : Check ["", "", "false"],
		$checks = '[
			["test", "facultative", true],
			["", "facultative", true],

			["", "nom", false],
			["maxime", "nom", true],
			["Maxime Fabre", "name", true],
			["Jean-Henri D\'Arbrœ des Écumaàñes", "prenom", true],
			["Anahkiasen66697", "nom", false],

			["", "number", false],
			["75", "number", true],
			["12.789", "nombre", true],
			["1,4535.79", "nombre", true],

			[" ", "hiyu", "true"],
			["test", "date", "true"]
		]';
		$checks = (array) json_decode($checks, true);
		return $checks;
	}

	// Tests ------------------------------------------------------- /

	/**
	 * @dataProvider passwords
	 */
	public function testPassword($password = null)
	{
		$valid = valid::password($password);

		if(strlen($password) < 4) self::assertFalse($valid);
		else self::assertTrue($valid);
	}

	/**
	 * @dataProvider passwords
	 */
	public function testPasswords($password = null, $password2 = null)
	{
		$valid = valid::passwords($password, $password2);

		if(strlen($password) < 4 or strlen($password2) < 4) self::assertFalse($valid);
		elseif($password != $password2) self::assertFalse($valid);
		else self::assertTrue($valid);
	}

	/**
	 * @dataProvider dates
	 */
	public function testDate($date = null, $isValid = false)
	{
		$valid = valid::date($date);
		$time = strtotime($date);

		if($isValid) self::assertEquals($valid, $time);
		else self::assertFalse($valid);
	}

	/**
	 * @dataProvider emails
	 */
	public function testEmail($email = null, $isValid = false)
	{
		$valid = valid::email($email);

		if($isValid) self::assertTrue($valid);
		else self::assertFalse($valid);

		$check = valid::check($email, 'email');
		if($isValid) self::assertTrue($check);
		else self::assertFalse($check);
	}

	/**
	 * @dataProvider urls
	 */
	public function testUrl($url = null, $isValid = false)
	{
		$valid = valid::url($url);

		if($isValid) self::assertTrue($valid);
		else self::assertFalse($valid);

		$check = valid::check($url, 'url');
		if($isValid) self::assertTrue($check);
		else self::assertFalse($check);
	}

	/**
	 * @dataProvider filenames
	 */
	public function testFilename($filename = null, $isValid = false)
	{
		$valid = valid::filename($filename);

		if($isValid) self::assertTrue($valid);
		else self::assertFalse($valid);
	}

	/**
	 * @dataProvider checks
	 */
	public function testCheck($string, $type, $isValid)
	{
		$check = valid::check($string, $type);
		if($isValid) self::assertTrue($check);
		else self::assertFalse($check);
	}
}
