<?php
use Cerberus\Buffer;
use Cerberus\File;

class BufferTests extends CerberusTests
{
  /**
   * Check if we're able to start an output buffer
   */
  public function testCanStartBuffer()
  {
    Buffer::start();

    $handlers = ob_list_handlers();
    $this->assertArrayHasKey('0', $handlers);
    $this->assertEquals('mb_output_handler', $handlers[0]);

    ob_end_flush();
  }

  /**
   * Check if we manage nested buffers, while accounting for
   * the 3 supplementary buffers started by Laravel
   */
  public function testCanNestBuffers()
  {
    Buffer::start();
      Buffer::start();
        Buffer::start();
          $handlers = ob_list_handlers();
        Buffer::end();
      Buffer::end();
    Buffer::end();

    $this->assertCount(5, $handlers);
  }

  /**
   * Check if we're able to properly close a buffer and get its content
   */
  public function testEndCanReturnBuffer()
  {
    $this->expectOutputString(null);

    Buffer::start();
      echo 'This is a test';
    $return = Buffer::end(true);

    $this->assertEquals('This is a test', $return);
  }

  /**
   * Check if content within an output buffer is properly released
   * when the buffer ends
   */
  public function testCanPrintOutBuffer()
  {
    $this->expectOutputString('This is a test');

    Buffer::start();
      echo 'This is a test';
    Buffer::end();
  }

  /**
   * Check if the shortcut for end(true) works for getting a buffer's content
   */
  public function testCanReturnBuffer()
  {
    $this->expectOutputString(null);

    Buffer::start();
      echo 'This is a test';
    $return = Buffer::get();

    $this->assertEquals('This is a test', $return);
  }

  /**
   * Check if we're able to release a buffer and have its content output
   */
  public function testCanReleaseBufferContent()
  {
    $this->expectOutputString('This is a test');

    Buffer::start();
      echo 'This is a test';
    Buffer::end();
  }

  /**
   * Check if we're able to load a file and execute its content
   */
  public function testCanLoadFilesWithBuffer()
  {
    $file = 'test.php';
    File::write($file, '<?php echo "This is a test" ?>');

    $load = Buffer::load($file);

    $this->assertEquals('This is a test', $load);
    File::remove($file);
  }

  /**
   * Check if we're able to change a buffer's mime type
   */
  public function testHeaders()
  {
    $type = Buffer::type('json');

    $this->assertEquals('Content-type: application/json; charset=UTF-8', $type);
  }
}
