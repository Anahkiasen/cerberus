<?php
use Cerberus\Toolkit\Buffer;

// Dependencies
use Cerberus\Toolkit\File;

class BufferTests extends CerberusTests
{
  public function testStart()
  {
    Buffer::start();

    $handlers = ob_list_handlers();
    self::assertArrayHasKey('0', $handlers);
    self::assertEquals('mb_output_handler', $handlers[0]);

    ob_end_flush();
  }

  public function testNested()
  {
    Buffer::start();
      Buffer::start();
        Buffer::start();
          $handlers = ob_list_handlers();
        Buffer::end();
      Buffer::end();
    Buffer::end();

    self::assertCount(6, $handlers);
  }

  public function testEndReturn()
  {
    self::expectOutputString(null);

    Buffer::start();
      echo 'This is a test';
    $return = Buffer::end(true);

    self::assertEquals('This is a test', $return);
  }

  public function testEndEcho()
  {
    self::expectOutputString('This is a test');

    Buffer::start();
      echo 'This is a test';
    Buffer::end();
  }

  public function testEndGet()
  {
    self::expectOutputString(null);

    Buffer::start();
      echo 'This is a test';
    $return = Buffer::get();

    self::assertEquals('This is a test', $return);
  }

  public function testEndFlush()
  {
    self::expectOutputString('This is a test');
    Buffer::start();
      echo 'This is a test';
    Buffer::end();
  }

  public function testLoad()
  {
    $file = 'test.php';
    file::write($file, '<?php echo "This is a test" ?>');

    $test = Buffer::load($file);

    self::assertEquals('This is a test', $test);
    file::remove($file);
  }

  public function testHeaders()
  {
    $type = Buffer::type('json');

    self::assertEquals('Content-type: application/json; charset=UTF-8', $type);
  }
}
