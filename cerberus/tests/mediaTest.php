<?php
class mediaTest extends PHPUnit_Framework_TestCase
{
	public function testImage()
	{
		$image = media::image('test.jpg');

		$matcher = array(
			'tag' => 'img',
			'attributes' => array('src' => 'assets/common/img/test.jpg'));
		self::assertTag($matcher, $image);
	}

	public function testAltAuto()
	{
		$image = media::image('test.jpg');

		$matcher = array(
			'tag' => 'img',
			'attributes' => array('alt' => 'test'));
		self::assertTag($matcher, $image);
	}

	public function testAlt()
	{
		$image = media::image('test.jpg', 'altTest');

		$matcher = array(
			'tag' => 'img',
			'attributes' => array('alt' => 'altTest'));
		self::assertTag($matcher, $image);
	}

	public function testAttributes()
	{
		$attributes = array('alt' => 'altTest', 'class' => 'classTest');
		$image = media::image('test.jpg', null, $attributes);

		$matcher = array(
			'tag' => 'img',
			'attributes' => $attributes);
		self::assertTag($matcher, $image);
	}

	public function testTimThumbSize()
	{
		$file = 'test.jpg';
		$size = 200;
		$tt = media::timthumb($file, $size, $size);

		self::assertEquals($tt, PATH_CORE.'class/plugins/timthumb.php?src=' .$file. '&w=' .$size. '&h=' .$size);
	}


}
?>