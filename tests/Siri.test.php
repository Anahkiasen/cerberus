<?php
use Cerberus\Modules\Siri;

class SiriTests extends CerberusTests
{
  public function tearDown()
  {
    \Config::set('application.language', 'fr');
  }

  public function testAccordArticleApostropheNormal()
  {
    $siri = Siri::accordArticle('poire', 'la');

    $this->assertEquals("la", $siri);
  }

  public function testAccordArticleApostrophe()
  {
    $siri = Siri::accordArticle('arbre', 'le');

    $this->assertEquals("l'", $siri);
  }

  public function testAccordArticleNormal()
  {
    \Config::set('application.language', 'en');
    $siri = Siri::accordArticle('pear', 'a');

    $this->assertEquals("a", $siri);
  }

  public function testAccordArticle()
  {
    \Config::set('application.language', 'en');
    $siri = Siri::accordArticle('apricot', 'a');

    $this->assertEquals("an", $siri);
  }
}