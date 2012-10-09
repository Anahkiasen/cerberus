<?php
use Cerberus\Toolkit\Language;

class LanguageTests extends CerberusTests
{
  public function testCurrent()
  {
    $current = Config::get('application.language');

    $this->assertEquals($current, Language::current());
  }

  public function testLocale()
  {
    $locale = Language::locale('en');
    $translatedString = strftime('%B', mktime(0, 0, 0, 1, 1, 2012));

    $this->assertEquals('en_US.UTF8', $locale);
    $this->assertEquals('January', $translatedString);
  }
}
