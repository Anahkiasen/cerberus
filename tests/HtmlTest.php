<?php
include '_start.php';

class HtmlTest extends CerberusTests
{
  // Tests --------------------------------------------------------- /

  public function testCanCreateImagesWithLink()
  {
    $image = $this->html->imageLink('foo', 'image.jpg', 'alt', array('class' => 'bar'));
    $matchLink = $this->matchLink('foo');
    $matchImage = array(
      'tag' => 'img',
      'attributes' => array(
        'src'  => 'http://test/en/image.jpg',
        'class' => 'bar',
        'alt'   => 'alt'
      ),
    );

    $this->assertHTML($matchLink, $image);
    $this->assertHTML($matchImage, $image);
  }

  public function testCanCreateAFavicon()
  {
    $favicon = $this->html->favicon('favicon.jpg');
    $matcher = array(
      'tag' => 'link',
      'attributes' => array('rel' => 'shortcut icon', 'href' => 'http://test/en/favicon.jpg'),
    );

    $this->assertHTML($matcher, $favicon);
  }

  public function testCanCreateResponsiveMetaTags()
  {
    $responsive = $this->html->responsiveTags();
    $matcher  = "<meta name='apple-mobile-web-app-capable' content='yes' />".PHP_EOL;
    $matcher .= "<meta name='apple-touch-fullscreen' content='yes' />".PHP_EOL;
    $matcher .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />".PHP_EOL;

    $this->assertEquals($matcher, $responsive);
  }

  public function testCanCreateATargetBlankLink()
  {
    $link = $this->html->toBlank('foo', 'bar', array('class' => 'bar'));
    $matcher = $this->matchLink('foo', 'bar', array('class' => 'bar', 'target' => '_blank'));

    $this->assertHTML($matcher, $link);
  }

  public function testCanCreateALinkToHome()
  {
    $link = $this->html->toHome('bar', array('class' => 'bar'));
    $matcher = $this->matchLink('', 'bar', array('class' => 'bar',));

    $this->assertHTML($matcher, $link);
  }
}