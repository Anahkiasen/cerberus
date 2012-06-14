<?php
class ContentTest extends PHPUnit_Framework_TestCase
{
	public function testStart()
	{
		content::start();

		$handlers = ob_list_handlers();
		self::assertArrayHasKey('0', $handlers);
		self::assertEquals('default output handler', $handlers[0]);

		ob_end_flush();
	}

	public function testNested()
	{
		content::start();
			content::start();
				content::start();
					$handlers = ob_list_handlers();
				content::end();
			content::end();
		content::end();

		self::assertCount(5, $handlers);
	}

	public function testEndReturn()
	{
		self::expectOutputString(null);

		content::start();
			echo 'This is a test';
		$return = content::end(true);

		self::assertEquals('This is a test', $return);
	}

	public function testEndFlush()
	{
		self::expectOutputString('This is a test');
		content::start();
			echo 'This is a test';
		content::end();
	}

	public function testLoad()
	{
		$file = 'test.php';
		f::write($file, '<?php echo "This is a test" ?>');

		$test = content::load($file);

		self::assertEquals('This is a test', $test);
		f::remove($file);
	}

	public function testHeaders()
	{
		$type = content::type('json');

		self::assertEquals('Content-type: application/json; charset=utf-8', $type);
	}
}