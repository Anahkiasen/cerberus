<?php
require('../class/core.init.php');
new Init('test', '../../');

class mediaTest extends PHPUnit_Framework_TestCase
{
	public function testImage()
	{
		$image = media::image('test.jpg');

		$matcher = array(
			'tag' => 'img',
			'attributes' => array('src' => '../../assets/common/img/test.jpg'));
		self::assertTag($matcher, $image);
	}

	public function testAttributeAuto()
	{
		$image = media::image('test.jpg');

		$matcher = array(
			'tag' => 'img',
			'attributes' => array('alt' => 'test.jpg'));
		self::assertTag($matcher, $image);
	}

	public function testAttribute()
	{
		$image = media::image('test.jpg', 'altText');

		$matcher = array(
			'tag' => 'img',
			'attributes' => array('alt' => 'altText'));
		self::assertTag($matcher, $image);
	}
}
?>