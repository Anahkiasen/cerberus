<?php
use Cerberus\Toolkit\Vendor;

class VendorTest extends CerberusTests
{
  // Helpers ------------------------------------------------------- /

  public static function createMatch($attributes)
  {
    return $match = array(
      'tag' => 'img',
      'attributes' => array_merge(array('alt' => 'placeholder'), $attributes)
    );
  }

  // Tests --------------------------------------------------------- /

  // Placeholder

  public function testPlaceholderSquare()
  {
    $placeholder = Vendor::placeholder(400);

    $match = self::createMatch(array('src' => 'http://placehold.it/400'));
    self::assertTag($match, $placeholder);
  }

  public function testPlaceholderRectangle()
  {
    $placeholder = Vendor::placeholder(400, 200);

    $match = self::createMatch(array('src' => 'http://placehold.it/400x200'));
    self::assertTag($match, $placeholder);
  }

  public function testPlaceholderText()
  {
    $placeholder = Vendor::placeholder(400, 200, array('text' => 'Placeholder'));

    $match = self::createMatch(array('src' => 'http://placehold.it/400x200&text=Placeholder', 'alt' => 'Placeholder'));
    self::assertTag($match, $placeholder);
  }

  public function testPlaceholderColors()
  {
    $placeholder = Vendor::placeholder(400, 200, array('bgc' => '000000', 'tc' => 'ffffff'));

    $match = self::createMatch(array('src' => 'http://placehold.it/400x200/000000/ffffff'));
    self::assertTag($match, $placeholder);
  }

  public function testPlaceholderFormat()
  {
    $placeholder = Vendor::placeholder(400, 200, array('format' => 'png'));

    $match = self::createMatch(array('src' => 'http://placehold.it/400x200.png'));
    self::assertTag($match, $placeholder);
  }

  public function testPlaceholderAll()
  {
    $placeholder = Vendor::placeholder(
      400,
      200,
      array(
        'text' => 'Placeholder',
        'format' => 'gif',
        'bgc' => '000000',
        'tc' => 'ffffff'));

    $match = self::createMatch(array(
      'src' => 'http://placehold.it/400x200/000000/ffffff&text=Placeholder.gif',
      'alt' => 'Placeholder'));

    self::assertTag($match, $placeholder);
  }
}