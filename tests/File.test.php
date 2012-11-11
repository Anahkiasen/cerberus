<?php
use Cerberus\File;

class FileTest extends CerberusTest
{
  // Tests --------------------------------------------------------- /

  public function testCanGetAPathBasename()
  {
    $withPath = File::filename('path/to/file/file.txt');
    $this->assertEquals('file.txt', $withPath);
  }

  public function testCanGetAFileBasename()
  {
    $withoutPath = File::filename('file.txt');
    $this->assertEquals('file.txt', $withoutPath);
  }

  public function testCanGetABareFileBasenameWithoutItsExtension()
  {
    $withoutExtension = File::filename('file');
    $this->assertEquals('file', $withoutExtension);
  }

  public function testCanGetAPathBasenameWithoutItsExtension()
  {
    $withPath = File::name('path/to/file/file.txt', false);
    $this->assertEquals('path/to/file/file', $withPath);
  }

  public function testCanRemovePathAndExtensionFromPath()
  {
    $withPathRemoved = File::name('path/to/file/file.txt');
    $this->assertEquals('file', $withPathRemoved);
  }

  public function testCanRemoveExtensionFromFile()
  {
    $withoutPath = File::name('file.txt');
    $this->assertEquals('file', $withoutPath);
  }

  public function testCanGetAFileExtension()
  {
    $file = File::extension('file.txt');
    $this->assertEquals('txt', $file);
  }

  public function testCanGetAPathExtension()
  {
    $file = File::extension('path/to/file.txt');
    $this->assertEquals('txt', $file);
  }

  public function testCanSanitizeAFilename()
  {
    $file = File::sanitize("La_pœetit&fl'oèr quÎ∫\ À∫´”„”’[å’å»[Ûå».txt");
    $this->assertEquals('la-poeetit-floer-qui-aaaua.txt', $file);
  }
}
