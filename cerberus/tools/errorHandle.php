<?php
/**
 * Sends or displays an error report
 *
 * @param  string    $errorType The type of the error
 * @param  string    $error     The message of the error
 * @param  string    $errorFile The file the error happened in
 * @param  int       $errorLine The line of the error
 * @return string    A full error report if local, true if production
 */
function errorHandle($errorType = 'Unknown', $error = 'Une erreur est survenue', $errorFile = __FILE__, $errorLine = __LINE__)
{
	// Setting up indentiation to trace the error
	$indentation = 0;

	// If function called with @, ignore
	if(error_reporting() == 0) return true;

	// Getting the error's backtrace
	$path = array_reverse(debug_backtrace());

	// Reading the error ---------------------------------------- */

	// Setting up the date
	$DEBUG['date'] = 'Une erreur est survenue &agrave; ' .date('H:i:s \l\e Y-m-d');

	// Setting up position
	if(class_exists('navigation')) $current = navigation::current();
	if(isset($current)) $DEBUG['date'] .= ' sur la page ['.navigation::current().']';

	// Defining error type
	switch ($errorType)
	{
		case E_DEPRECATED:
		case E_STRICT:
		case 'advice':
		$DEBUG['error'] = 'Advice';
		break;

		case E_NOTICE:
		case E_USER_NOTICE:
		case 'notice':
		$DEBUG['error'] = 'Notice';
		break;

		case E_WARNING:
		case E_USER_WARNING:
		case 'warning':
		$DEBUG['error'] = 'Warning';
		break;

		case E_ERROR:
		case E_USER_ERROR:
		case 'fatal':
		$DEBUG['error'] = 'Fatal Error';
		break;

		case 'SQL':
		$DEBUG['error'] = 'MySQL';
		break;

		default:
		$DEBUG['error'] = 'Unknown';
		break;
	}

	$DEBUG['error'] = '
	<h3>[' .$DEBUG['error']. '] ' .$error. '</h3>
	<h4>' .basename($errorFile). ':' .$errorLine. '</h4>';

	// Reading backtrace ---------------------------------------- */

	foreach($path as $id_file => $info)
	{
		// Where the error came from
		if(isset($info['file'], $info['line'])) $thisPath[] = '<em>' .basename($info['file']). '</em> &agrave; la ligne <strong>' .$info['line']. '</strong>';
		if(isset($info['type'], $info['function'], $info['class'])) $thisPath[] = 'La fonction appel&eacute;e &eacute;tait <strong>' .$info['class'].$info['type'].$info['function']. '</strong>';
		else
		{
			if(isset($info['function']) and $info['function'] != 'errorHandle') $thisPath[] = 'La fonction appel&eacute;e &eacute;tait <strong>' .$info['function']. '</strong>';
			if(isset($info['class'])) $thisPath[] = 'La classe appel&eacute;e &eacute;tait <strong>' .$info['class']. '</strong>';
		}

		// What arguments were used
		if(isset($info['args']) and !empty($info['args']) and $info['function'] != 'errorHandle')
		{
			foreach($info['args'] as $key => $value)
			{
				// Displaying according to the kind of argument (array, string, file)
				if(in_array($info['function'], array('include', 'include_once'))) $info['args'][$key] = '"' .basename($value). '"';
				elseif(is_array($value)) $info['args'][$key] = '<pre>' .print_r($value, TRUE). '</pre>';
				else $info['args'][$key] = '"' .$value. '"';
			}

			// Displaying formatted arguments
			$parametres = 'Ses param&egrave;tres &eacute;taient : ';
			if(count($info['args']) > 1) $parametres .= '<br />';
			$parametres .= '<em>' .implode(', ', $info['args']). '</em>';
			$thisPath[] = $parametres;
		}

		$DEBUG['path_' .$id_file] = '<div style="padding-left:' .($id_file * 25 + 10). 'px">' .implode('<br />'.PHP_EOL."\t", $thisPath). '</div>';
		$thisPath = array();
	}

	// Displaying the full report ------------------------------ */

	// Prints out what we gathered
	$DEBUG = '<div class="alert alert-error cerberus_debug">' .implode('', $DEBUG). '</div>';

	// If in local, just display the error, if in production, send it
	if(defined('LOCAL'))
		if(!LOCAL and navigation::$page != 'admin')
		{
			$titre_email = config::get('sitename');
			$titre_email = $titre_email ? 'Cerberus - ' .$titre_email : 'CerberusDebug';

			$mailTitle = '[DEBUG] ' .basename($errorFile). '::' .$errorLine;
			$mail = new smail(config::get('developper.mail', 'maxime@stappler.fr'), $mailTitle, $DEBUG);
			$mail->setExpediteur($titre_email, config::get('mail'));
			$mail->messageHTML();
			$mail->send();

			return true;
		}

	// Fallback if no idea whether local or not - just display the error
	echo $DEBUG;
}
?>
