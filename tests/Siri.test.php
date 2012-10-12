<?php
use Cerberus\Modules\Siri;

class SiriTests extends CerberusTests
{
  public function tearDown()
  {
    \Config::set('application.language', 'fr');
  }

  public function wrapAlert($state, $text)
  {
    return $state
      ? '<div class="alert alert-success">' .$text. '</div>'
      : '<div class="alert alert-error">' .$text. '</div>';
  }

  public function restful()
  {
    return array(
      array('category', 'foo', 'create', true, 'La catégorie &laquo; foo &raquo; a bien été créée'),
      array('user', 'foo', 'delete', false, 'L\'utilisateur &laquo; foo &raquo; n\'a pas pu être supprimé'),
    );
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

  /**
   * @dataProvider restful
  */
  public function testRestful($page, $object, $verb, $state, $expected)
  {
    $siri = Siri::restful($page, $object, $verb, $state);
    $expected = $this->wrapAlert($state, $expected);

    $this->assertEquals($expected, $siri);
  }

  public function testAdd()
  {
    $siri = Siri::add('user');

    $this->assertEquals('Ajouter un utilisateur', $siri);
  }

  public function testAddAccord()
  {
    $siri = Siri::add('category');

    $this->assertEquals('Ajouter une catégorie', $siri);
  }

  public function testNothing()
  {
    $siri = Siri::nothing('user');

    $this->assertEquals('Aucun utilisateur à afficher', $siri);
  }
}