<?php
use Cerberus\Buffer;

// Dependencies
use Cerberus\File;

class BufferTests extends CerberusTests
{
  /**
   * Check if we're able to start an output buffer
   */
  public function testStart()
  {
    Buffer::start();

    $handlers = ob_list_handlers();
    self::assertArrayHasKey('0', $handlers);
    self::assertEquals('mb_output_handler', $handlers[0]);

    ob_end_flush();
  }

  /**
   * Check if we manage nested buffers, while accounting for
   * the 3 supplementary buffers started by Laravel
   */
  public function testNested()
  {
    Buffer::start();
      Buffer::start();
        Buffer::start();
          $handlers = ob_list_handlers();
        Buffer::end();
      Buffer::end();
    Buffer::end();

    self::assertCount(5, $handlers);
  }

  /**
   * Check if we're able to properly close a buffer and get its content
   */
  public function testEndReturn()
  {
    self::expectOutputString(null);

    Buffer::start();
      echo 'This is a test';
    $return = Buffer::end(true);

    self::assertEquals('This is a test', $return);
  }

  /**
   * Check if content within an output buffer is properly released
   * when the buffer ends
   */
  public function testEndEcho()
  {
    self::expectOutputString('This is a test');

    Buffer::start();
      echo 'This is a test';
    Buffer::end();
  }

  /**
   * Check if the shortcut for end(true) works for getting a buffer's content
   */
  public function testEndGet()
  {
    self::expectOutputString(null);

    Buffer::start();
      echo 'This is a test';
    $return = Buffer::get();

    self::assertEquals('This is a test', $return);
  }

  /**
   * Check if we're able to release a buffer and have its content output
   */
  public function testEndFlush()
  {
    self::expectOutputString('This is a test');

    Buffer::start();
      echo 'This is a test';
    Buffer::end();
  }

  /**
   * Check if we're able to load a file and execute its content
   */
  public function testLoad()
  {
    $file = 'test.php';
    File::write($file, '<?php echo "This is a test" ?>');

    $test = Buffer::load($file);

    self::assertEquals('This is a test', $test);
    File::remove($file);
  }

  /**
   * Check if we're able to change a buffer's mime type
   */
  public function testHeaders()
  {
    $type = Buffer::type('json');

    self::assertEquals('Content-type: application/json; charset=UTF-8', $type);
  }
}
