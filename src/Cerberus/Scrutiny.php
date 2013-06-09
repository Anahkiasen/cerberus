<?php
namespace Cerberus;

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Foundation\Testing\TestCase;

class Scrutiny extends TestCase
{
  /**
   * Creates the application.
   *
   * @return Symfony\Component\HttpKernel\HttpKernelInterface
   */
  public function createApplication()
  {
    $unitTesting     = true;
    $testEnvironment = 'testing';

    return require __DIR__.'/../../../../../bootstrap/start.php';
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// HELPERS ///////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Check if a particular number of items exist in a page
   */
  protected function assertNthItemsExist(Crawler $crawler, $number, $select, $message = null)
  {
    $items = $crawler->filter($select);
    if (!$message) $message = sizeof($items). " instead of $number of `$select` were found in the page";

    $this->assertCount($number, $items, $message);
  }

  /**
   * Check if some items exist in a page
   */
  protected function assertItemsExist(Crawler $crawler, $select, $message = null)
  {
    if (!$message) $message = "The items `$select` were no found in the page";

    $this->assertNotCount(0, $crawler->filter($select), $message);
  }

  /**
   * Get the Crawler for a page
   */
  protected function getPage($url = null, $method = 'GET')
  {
    $response = $this->call($method, '/'.$url);
    $this->assertResponseOk();

    return new Crawler($response->getContent());
  }

  /**
   * Check if a page is correctly loaded
   */
  protected function assertIsPage($url, $title)
  {
    $crawler = $this->getPage($url);

    $this->assertTagContains($crawler, 'title', $title);
  }

  /**
   * Check if a tag contains something
   */
  protected function assertTagContains(Crawler $crawler, $tag, $content)
  {
    $tag = utf8_decode($crawler->filter($tag)->text());

    $this->assertContains($content, $tag);
  }
}
