<?php
class CerberusTests extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->html = $this->getHTML();
  }

  // Matchers ------------------------------------------------------ /

  public function matchLink($href)
  {
    return array(
      'tag' => 'a',
      'attributes' => array('href' => 'http://test/en/'.$href),
    );
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