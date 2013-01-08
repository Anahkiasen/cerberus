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
}