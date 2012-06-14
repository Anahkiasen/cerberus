<?php
class TimerTest extends PHPUnit_Framework_TestCase
{
	private static $timer = 'testTimer';

	public function testStart()
	{
		Timer::start(self::$timer);

		// Timer started
		self::assertArrayHasKey(self::$timer, Timer::$running);

		// Timer of correct type
		$timer = a::get(Timer::$running, self::$timer);
		self::assertInternalType('float', $timer);
	}

	/**
	 * @depends testStart
	 */
	public function testStop()
	{
		Timer::stop(self::$timer);

		// Timer stopped
		self::assertArrayHasKey(   self::$timer, Timer::$finished);
		self::assertArrayNotHasKey(self::$timer, Timer::$running);

		// Timer is a float
		$timer = a::get(Timer::$finished, self::$timer);
		self::assertInternalType('float', $timer);
	}

	public function testStartAnonymous()
	{
		// Start anonymous timer
		Timer::start();

		// Get its number
		$timerName = end(array_keys(Timer::$running));
		self::assertEquals($timerName, 1);

		// Check if it's a float
		$timer = a::get(Timer::$running, $timerName);
		self::assertInternalType('float', $timer);

		return $timerName;
	}

	/**
	 * @depends testStartAnonymous
	 */
	public function testStopAnonymous($timerName = null)
	{
		// Get before/after of the timer
		$beforeValue = a::get(Timer::$running, $timerName);
		$before = Timer::$running;
		Timer::stop();
		$after = Timer::$running;
		$afterValue = a::get(Timer::$running, $timerName);

		self::assertArrayHasKey($timerName, $before);
		self::assertArrayNotHasKey($timerName, $after);
		self::assertArrayHasKey($timerName, Timer::$finished);
		self::assertNotEquals($beforeValue, $afterValue);
	}

	public function testGet()
	{
		$timerName = 'testTimer2';

		// Test running
		Timer::start($timerName);
		self::assertArrayHasKey($timerName, Timer::$running);
		$running = Timer::getRunning($timerName);
		self::assertEquals($running, Timer::$running[$timerName]);

		// Test Finished
		Timer::stop($timerName);
		self::assertArrayNotHasKey($timerName, Timer::$running);
		self::assertArrayHasKey($timerName, Timer::$finished);
		$finished = Timer::getFinished($timerName);
		self::assertEquals($finished, Timer::$finished[$timerName]);
	}

	public function testStopAll()
	{
		$timers = array('timer1', 'timer2', 'timer3');
		foreach($timers as $t) timer::start($t);

		Timer::close();
		self::assertEmpty(Timer::$running);
		foreach($timers as $t) self::assertArrayHasKey($t, Timer::$finished);
	}

	public function testRender()
	{
		// Test closing of unclosed timers
		Timer::start('unclosed');
		content::start();
			Timer::show();
		$render = content::end(true);
		self::assertEmpty(Timer::$running);

		// Test number of elements
		$timers = sizeof(Timer::$finished);
		$matcher = array(
			'tag' => 'pre',
			'children' => array(
				'count' => $timers,
				'only' => array(
					'tag' => 'p')));
		self::assertTag($matcher, $render);
	}
}