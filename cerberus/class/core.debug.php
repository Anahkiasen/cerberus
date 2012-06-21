<?php
class Debug
{
	/**
	 * The current Exception
	 * @var object
	 */
	private static $e            = null;

	/**
	 * The current error type
	 * @var string
	 */
	private static $errorType    = null;

	/**
	 * The current error code
	 * @var integer
	 */
	private static $errorCode    = 0;

	/**
	 * The current error message
	 * @var string
	 */
	private static $errorMessage = null;

	/**
	 * Facultative additional params to display in the debug report
	 * @var array
	 */
	private static $params       = array();

	/**
	 * Decides wether we should print or send the error log
	 *
	 * @param Exception $exception An exception that occured
	 * @param integer   $type      An error code or type
	 */
	public static function handle($exception, $message = null, $type = null, $params = array())
	{
		// Error type
		self::$e = $exception;
		self::$errorType = self::errorType($type);

		// Message
		self::$errorMessage = $message
			? $message
			: self::$e->getMessage();

		// Additional parameters
		self::$params = $params;

		// Displaying error or sending it
		if(defined('LOCAL') and LOCAL) echo self::render();
		else self::send();
	}

	/**
	 * Display the error backtrace
	 */
	private static function render()
	{
		$e = self::$e;

		// Getting error code
		$code = $e->getCode();
		$code = !empty($code) ? '['.$code.'] ' : null;

		// Displaying error header
		$render = '<h1>' .self::$errorType. ' : ' .$code.self::$errorMessage. '</h1>';
		$render .= '
		<h2>' .basename($e->getFile()). '[' .$e->getLine(). ']
		at <ins>' .date('H:i:s').'</ins>
		the <ins>' .date('Y-m-d'). '</ins></h2>';

		// Crawling backtrace
		foreach($e->getTrace() as $i => $t)
		{
			// File
			$render .= '<div style="margin-left:' .($i * 25). 'px">';
				$render .= '<h3>' .basename(a::get($t, 'file')). '[' .a::get($t, 'line'). ']</h3>';

				// Arguments
				if(!empty($t['args'])) $t['args'] = self::implodeParams($t['args']);
				else $t['args'] = null;

				// Class and function
				if(isset($t['function']) and isset($t['class']))
					$render .= '<p>' .$t['class'].$t['type'].$t['function'].'(' .$t['args']. ')</p>';

				elseif(isset($t['function']))
					$render .= '<p>' .$t['function'].'(' .$t['args']. ')</p>';
			$render .= '</div>';
		}

		// Environnement
		if(self::$params and is_array(self::$params))
		{
			$render .= '<pre style="word-wrap: break-word">';
			foreach(self::$params as $param => $value)
				$render .= '<p><strong>' .$param. '</strong> :<br/>' .htmlentities($value).'</p>';
			$render .= '</pre>';
		}

		return '<div class="alert alert-block cerberus-debug">' .$render. '</div>';
	}

	/**
	 * Send the error log
	 */
	private static function send()
	{
		$e = self::$e;

		// Setting the mail's title
		$mailTitle = config::get('sitename');
		$mailTitle = $mailTitle ? 'Cerberus - ' .$mailTitle : 'CerberusDebug';

		$mailObject = '[DEBUG] ' .basename($e->getFile()). '::' .$e->getLine();

		$mail = new smail(config::get('developper.mail', 'maxime@stappler.fr'), $mailObject, self::render($e));
		$mail->setExpediteur($mailTitle, config::get('mail'));
		$mail->messageHTML();
		$mail->send();
	}

	private static function implodeParams($params)
	{
		$params = a::simplify($params, false);
		return "'" .implode("', '", $params). "'";
	}

	/**
	 * Getting a readable error type
	 *
	 * @param  string $errorType A type of error
	 * @return string            A readable type or error
	 */
	private static function errorType($errorType)
	{
		$errorType = strtolower($errorType);
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

			case 'sql':
			$error = 'MySQL';
			break;

			default:
			$error = 'Unknown';
			break;
		}
		return $error;
	}
}
