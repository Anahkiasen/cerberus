<?php
class Debug extends Exception
{
	private $errorType = NULL;

	/**
	 * Decides wether we should print or send the error log
	 *
	 * @param Exception $exception An exception that occured
	 * @param integer   $type      An error code or type
	 */
	public function __construct($exception, $type = NULL)
	{
		// Building Exception
		parent::__construct($exception);

		// Error type
		$this->errorType = $this->errorType($type);

		// Displaying error
		if(LOCAL) $this->render();
		else $this->send();
	}

	/**
	 * Send the error log
	 */
	private function send()
	{
		// Setting the mail's title
		$mailTitle = config::get('sitename');
		$mailTitle = $mailTitle ? 'Cerberus - ' .$mailTitle : 'CerberusDebug';

		$mailObject = '[DEBUG] ' .basename($this->getFile()). '::' .$this->getLine();

		$mail = new smail(config::get('developper.mail', 'maxime@stappler.fr'), $mailObject, $DEBUG);
		$mail->setExpediteur($mailTitle, config::get('mail'));
		$mail->messageHTML();
		$mail->send();
	}

	/**
	 * Display the error backtrace
	 */
	private function render()
	{
		// Getting error code
		$code = $this->getCode();
		$code = !empty($code) ? '['.$code.'] ' : NULL;


		echo '
		<h1>' .$this->errorType. ' : ' .$code.$this->getMessage(). '</h1>
		<h2>' .basename($this->getFile()). '[' .$this->getLine(). '] at <ins>' .date('H:i:s \t\h\e Y-m-d'). '</ins></h2>';

		// Crawling backtrace
		foreach($this->getTrace() as $i => $t)
		{
			// File
			echo '<div style="margin-left:' .($i * 25). 'px">';
				echo '<h3>' .basename($t['file']). '[' .$t['line']. ']</h3>';

				// Arguments
				if(!empty($t['args'])) $t['args'] = "'" .implode("', '", $t['args']). "'";
				else $t['args'] = NULL;

				// Class and function
				if(isset($t['function']) and isset($t['class']))
					echo '<p>' .$t['class'].$t['type'].$t['function'].'(' .$t['args']. ')</p>';

				elseif(isset($t['function']))
					echo '<p>' .$t['function'].'(' .$t['args']. ')</p>';
			echo '</div>';
		}
	}

	private function errorType($errorType)
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