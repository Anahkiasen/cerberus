<?php
/**
 * Custom class to handle errors
 */
namespace Cerberus;

use \Message;
use \Bundle;

class ErrorHandler
{
  /**
   * Handles an Exception
   *
   * @param Exception $exception The exception
   */
  public static function handle($exception, $website = 'Cerberus')
  {
    static::sendMail($exception, $website);
  }

  /**
   * Send a notification email
   *
   * @param Exception $exception
   *
   * @return boolean
   */
  protected static function sendMail($exception, $website)
  {
    if (!class_exists('Message')) Bundle::start('messages');

    // Create email body
    $body = static::buildbody($exception);

    // Send notification email
    $message = Message::to(['ehtnam6@gmail.com', 'maxime@stappler.fr'])
      ->from('cerberus@stappler.fr', 'Cerberus')
      ->subject($website. ' : ' .$exception->getMessage())
      ->body($body)
      ->html(true)
      ->send();

    return $message->was_sent();
  }

  /**
   * Build body from an Exception
   *
   * @param Exception $exception
   *
   * @return string
   */
  protected static function buildBody($exception)
  {
    $message = $exception->getMessage();
    $file    = $exception->getFile();

    return
      '<html>
        <head>
          <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
        </head>
        <body style="padding: 2rem">
          <h2>Unhandled Exception</h2>
          <h3>Message:</h3>
          <pre>' .$message. '</pre>
          <h3>Location:</h3>
          <pre>' .$file.' on line '.$exception->getLine(). '</pre>
          <h3>Stack Trace:</h3>
          <pre>' .$exception->getTraceAsString(). '</pre>
        </body>
      </html>';
  }

  /**
   * Format a log friendly message from the given exception.
   *
   * @param  Exception  $e
   * @return string
   */
  protected static function getPlaceOf($exception)
  {
    return $exception->getMessage().' in '.$exception->getFile().' on line '.$exception->getLine();
  }
}