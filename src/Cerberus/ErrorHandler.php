<?php
/**
 * Custom class to handle errors
 */
namespace Cerberus;

use Exception;
use Mail;
use View;

class ErrorHandler
{
  /**
   * Handles an Exception
   *
   * @param Exception $exception The exception
   */
  public function __construct(Exception $exception, $website = 'Cerberus')
  {
    $this->exception = $exception;
    $this->website   = $website;
  }

  /**
   * Render the Exception
   *
   * @return string
   */
  public function render()
  {
    return View::make('cerberus::exception', $this->getData());
  }

  /**
   * Send a notification email
   *
   * @param Exception $exception
   *
   * @return boolean
   */
  protected function sendMail()
  {
    $data    = $this->getData();
    $website = $data['website'];

    // Send notification email
    $message = Mail::send('cerberus::exception', $data, function($mail) use($data) {
      $mail->to('maxime@stappler.fr');
      $mail->from('cerberus@laravel.fr', 'Cerberus');
      $mail->subject('['.$data['website'].'] ' .$data['error']);
    });
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  protected function formatTraceArguments($trace)
  {
    foreach ($trace as $id => $stack) {
      foreach ($stack['args'] as $key => $arg) {
        if (is_array($arg)) {
          $trace[$id]['args'][$key] = d($arg);
        } elseif (is_object($arg)) {
          $trace[$id]['args'][$key] = get_class($arg);
        }
      }
    }

    return $trace;
  }

  /**
   * Get the Exception's data
   *
   * @return [type] [description]
   */
  protected function getData()
  {
    return array(
      'error'   => $this->exception->getMessage(),
      'file'    => $this->exception->getFile(),
      'website' => $this->website,
      'line'    => $this->exception->getLine(),
      'trace'   => $this->formatTraceArguments($this->exception->getTrace()),
    );
  }
}