<?php
class Debug
{
	private static $errorType = NULL;

	/**
	 * Decides wether we should print or send the error log
	 *
	 * @param Exception $exception An exception that occured
	 * @param integer   $type      An error code or type
	 */
	static public function handle($exception, $type = NULL)
	{
		// Error type
		self::$errorType = self::errorType($type);

		// Displaying error or sending it
		if(LOCAL) echo self::render($exception);
		else self::send($exception);
	}

	/**
	 * Display the error backtrace
	 */
	private static function render($e)
	{
		// Getting error code
		$code = $e->getCode();
		$code = !empty($code) ? '['.$code.'] ' : NULL;

		// Displaying error header
		$render = '
		<h1>' .self::$errorType. ' : ' .$code.$e->getMessage(). '</h1>
		<h2>' .basename($e->getFile()). '[' .$e->getLine(). '] at <ins>' .date('H:i:s \t\h\e Y-m-d'). '</ins></h2>';

		// Crawling backtrace
		foreach($e->getTrace() as $i => $t)
		{
			// File
			$render .= '<div style="margin-left:' .($i * 25). 'px">';
				$render .= '<h3>' .basename($t['file']). '[' .$t['line']. ']</h3>';

				// Arguments
				if(!empty($t['args'])) $t['args'] = "'" .implode("', '", $t['args']). "'";
				else $t['args'] = NULL;

				// Class and function
				if(isset($t['function']) and isset($t['class']))
					$render .= '<p>' .$t['class'].$t['type'].$t['function'].'(' .$t['args']. ')</p>';

				elseif(isset($t['function']))
					$render .= '<p>' .$t['function'].'(' .$t['args']. ')</p>';
			$render .= '</div>';
		}

		return '<div class="alert alert-block cerberus-debug">' .$render. '</div>';
	}

	/**
	 * Send the error log
	 */
	private static function send($e)
	{
		// Setting the mail's title
		$mailTitle = config::get('sitename');
		$mailTitle = $mailTitle ? 'Cerberus - ' .$mailTitle : 'CerberusDebug';

		$mailObject = '[DEBUG] ' .basename($e->getFile()). '::' .$e->getLine();

		$mail = new smail(config::get('developper.mail', 'maxime@stappler.fr'), $mailObject, self::render($e));
		$mail->setExpediteur($mailTitle, config::get('mail'));
		$mail->messageHTML();
		$mail->send();
	}

	/**
	 * Getting a readable error type
	 *
	 * @param  string $errorType A type of error
	 * @return string            A readable type or error
	 */
	private static function errorType($errorType)
	{
		switch ($errorType)
		{
			case E_DEPRECATED:
			case E_STRICT:
			case 'advice':
			$error = 'Advice';
			break;

			case E_NOTICE:
			case E_USER_NOTICE:
			case 'notice':
			$error = 'Notice';
			break;

			case E_WARNING:
			case E_USER_WARNING:
			case 'warning':
			$error = 'Warning';
			break;

			case E_ERROR:
			case E_USER_ERROR:
			case 'fatal':
			$error = 'Fatal Error';
			break;

			case 'SQL':
			$error = 'MySQL';
			break;

			default:
			$error = 'Unknown';
			break;
		}
		return $error;
	}
}