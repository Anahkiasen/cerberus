<?php
class CerberusTests extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->html = $this->getHTML();
  }

  // Matchers ------------------------------------------------------ /

  public function matchLink($href, $text = null, $attributes = array())
  {
    $link = array('href' => 'http://test/en/'.$href);
    $attributes = array_merge($link, $attributes);

    $link = array(
      'tag' => 'a',
      'attributes' => $attributes,
    );

    if ($text) $link['content'] = $text;

    return $link;
  }

  // Helpers ------------------------------------------------------- /

  public function assertHTML($matcher, $input)
  {
    $this->assertTag(
      $matcher,
      $input,
      "Failed asserting that the HTML matches the provided format :\n\t"
        .$input."\n\t"
        .json_encode($matcher));
  }

  // Mockery ------------------------------------------------------- /

  private function getHTML()
  {
    $html = new Cerberus\HTML($this->getUrl());

    return $html;
  }

  private function getUrl()
  {
    $url = Mockery::mock('Illuminate\Routing\UrlGenerator');
    $url->shouldReceive('to')->andReturnUsing(function($url) {
      return 'http://test/en/'.$url;
    });
    $url->shouldReceive('asset')->andReturnUsing(function($url) {
      return 'http://test/en/'.$url;
    });

    return $url;
  }
}