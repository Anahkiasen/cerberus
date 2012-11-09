<?php
use Cerberus\Language;

class LanguageTests extends CerberusTests
{
  public function testCanGetCurrentLanguage()
  {
    $current = Config::get('application.language');

    $this->assertEquals($current, Language::current());
  }

  public function testCanSetLocaleFromLanguage()
  {
    $locale = Language::locale('en');
    $translatedString = strftime('%B', mktime(0, 0, 0, 1, 1, 2012));

    $this->assertContains($locale, array('en_US.UTF8', 'en_US'));
    $this->assertEquals('January', $translatedString);
  }
}
