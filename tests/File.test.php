<?php
use Cerberus\Toolkit\File;

class FileTests extends CerberusTests
{
  // Tests --------------------------------------------------------- /

  public function testBasenameWithPath()
  {
    $withPath = File::filename('path/to/file/file.txt');
    $this->assertEquals('file.txt', $withPath);
  }

  public function testBasenameWithoutPath()
  {
    $withoutPath = File::filename('file.txt');
    $this->assertEquals('file.txt', $withoutPath);
  }

  public function testBasenameWithoutExtension()
  {
    $withoutExtension = File::filename('file');
    $this->assertEquals('file', $withoutExtension);
  }

  public function testFilenameWithPath()
  {
    $withPath = File::name('path/to/file/file.txt', false);
    $this->assertEquals('path/to/file/file', $withPath);
  }

  public function testFilenameWithPathRemoved()
  {
    $withPathRemoved = File::name('path/to/file/file.txt');
    $this->assertEquals('file', $withPathRemoved);
  }

  public function testFilenameWithoutPath()
  {
    $withoutPath = File::name('file.txt');
    $this->assertEquals('file', $withoutPath);
  }

  public function testExtension()
  {
    $file = File::extension('file.txt');
    $this->assertEquals('txt', $file);
  }

  public function testExtensionWithPath()
  {
    $file = File::extension('path/to/file.txt');
    $this->assertEquals('txt', $file);
  }

  public function testSanitize()
  {
    $file = File::sanitize("La pœetit fl'oèr quÎ∫\ À∫´”„”’[å’å»[Ûå».txt");
    $this->assertEquals('la-poeetit-floer-qui-aaaua.txt', $file);
  }
}
