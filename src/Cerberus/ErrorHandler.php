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
    $this->sendMail($exception, $website);
  }

  /**
   * Send a notification email
   *
   * @param Exception $exception
   *
   * @return boolean
   */
  protected function sendMail(Exception $exception, $website)
  {
    $data = array(
      'error' => $exception->getMessage(),
      'file'  => $exception->getFile(),
      'line'  => $exception->getLine(),
      'trace' => $exception->getTraceAsString(),
    );

    // Send notification email
    $message = Mail::send('cerberus::exception', $data, function($mail) use($data, $website) {
      $mail->to('maxime@stappler.fr')->cc('ehtnam6@gmail.com');
      $mail->from('cerberus@stappler.fr', 'Cerberus');
      $mail->subject($website. ' : ' .$data['error']);
    });

    return View::make('cerberus::exception', $data);
  }
}