<?php
use Cerberus\HTML;

class HTMLTest extends CerberusTest
{
  // Tests --------------------------------------------------------- /

  public function testCanCreateImagesWithLink()
  {
    $image = HTML::image_link('foo', 'image.jpg', 'alt', array('class' => 'bar'));
    $matcher = '<a href="http://test/en/foo"><img src="http://test/image.jpg" class="bar" alt="alt"></a>';

    $this->assertEquals($matcher, $image);
  }

  public function testCanCreateATargetBlankLink()
  {
    $link = HTML::blank_link('foo', 'bar', array('class' => 'bar'));
    $matcher = '<a href="http://test/en/foo" class="bar" target="_blank">bar</a>';

    $this->assertEquals($matcher, $link);
  }

  public function testCanCreateALinkToHome()
  {
    $link = HTML::link_to_home('bar', array('class' => 'bar'));
    $matcher = '<a href="http://test/en/" class="bar">bar</a>';

    $this->assertEquals($matcher, $link);
  }

  public function testCanCreateAFavicon()
  {
    $favicon = HTML::favicon('favicon.jpg');
    $matcher = "<link href='http://test/favicon.jpg' rel='shortcut icon' />";

    $this->assertEquals($matcher, $favicon);
  }

  public function testCanCreateResponsiveMetaTags()
  {
    $responsive = HTML::responsive();
    $matcher  = "<meta name='apple-mobile-web-app-capable' content='yes' />".PHP_EOL;
    $matcher .= "<meta name='apple-touch-fullscreen' content='yes' />".PHP_EOL;
    $matcher .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />".PHP_EOL;

    $this->assertEquals($matcher, $responsive);
  }
}
