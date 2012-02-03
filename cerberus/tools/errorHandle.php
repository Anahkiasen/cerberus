<?php
function errorHandle($errorType = 'Unknown', $error = 'Une erreur est survenue', $errorFile = __FILE__, $errorLine = __LINE__)
{	
	// Récupération du chemin du fichier
	global $desired;
	$path = array_reverse(debug_backtrace());
	$indentation = 0;
	
	// Date et position de l'erreur
	$currentPage = (isset($desired)) ? ' sur la page ['.$desired->current().']' : NULL;
	$DEBUG['date'] = 'Une erreur est survenue &agrave; ' .date('H:i:s \l\e Y-m-d').$currentPage. '<br />';
	if(!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);
	
	// Type d'erreur
	switch ($errorType)
	{
		case E_DEPRECATED:
		case E_STRICT:
		$DEBUG['error'] = 'Advice';
		break;
		
		case E_NOTICE:
		case E_USER_NOTICE:
		$DEBUG['error'] = 'Notice';
		break;
		
		case E_WARNING:
		case E_USER_WARNING:
		$DEBUG['error'] = 'Warning';
		break;
		
		case E_ERROR:
		case E_USER_ERROR:
		$DEBUG['error'] = 'Fatal Error';
		break;
		
		case 'SQL':
		$DEBUG['error'] = 'MySQL';
		break;
		
		default:
		$DEBUG['error'] = 'Unknown';
		break;
	}
		
	f::append('cerberus/cache/error.log', f::filename($errorFile). '::' .$errorLine. ' - ' .$DEBUG['error']. ': ' .$error.PHP_EOL);
	$DEBUG['error'] = '<h3>[' .$DEBUG['error']. '] ' .$error. '</h3>
	<h4>' .f::filename($errorFile). ':' .$errorLine. '</h4>';
	
	foreach($path as $id_file => $info)
	{
		// Provenance de l'erreur
		if(isset($info['file'], $info['line'])) $thisPath[] = '<em>' .f::filename($info['file']). '</em> &agrave; la ligne <strong>' .$info['line']. '</strong>';
		if(isset($info['type'], $info['function'], $info['class'])) $thisPath[] = 'La fonction appel&eacute;e &eacute;tait <strong>' .$info['class'].$info['type'].$info['function']. '</strong>';
		else
		{
			if(isset($info['function']) and $info['function'] != 'errorHandle') $thisPath[] = 'La fonction appel&eacute;e &eacute;tait <strong>' .$info['function']. '</strong>';
			if(isset($info['class'])) $thisPath[] = 'La classe appel&eacute;e &eacute;tait <strong>' .$info['class']. '</strong>';
		}
		
		// Arguments utilisés
		if(isset($info['args']) and !empty($info['args']) and $info['function'] != 'errorHandle')
		{
			foreach($info['args'] as $key => $value)
			{
				// Types d'arguments
				if(in_array($info['function'], array('include', 'include_once'))) $info['args'][$key] = '"' .f::filename($value). '"';
				elseif(is_array($value)) $info['args'][$key] = '<pre>' .print_r($value, TRUE). '</pre>';
				else $info['args'][$key] = '"' .$value. '"';
			}
			
			// Affichage des arguments (saut de ligne si plus d'un)
			$parametres = 'Ses param&egrave;tres &eacute;taient : ';
			if(count($info['args']) > 1) $parametres .= '<br />';
			$parametres .= '<em>' .implode(', ', $info['args']). '</em>';
			$thisPath[] = $parametres;
		}
		
		$DEBUG['path_' .$id_file] = '<div style="padding-left:' .($id_file * 25 + 10). 'px">' .implode('<br />', $thisPath). '</div>';
		$thisPath = array();
	}
	
	/* 
	########################################
	########## AFFICHAGE DE L'ERREUR #######
	########################################
	*/
	
	// Rassemblement des informations sur l'erreur
	$DEBUG = '<div class="cerberus_debug">' .implode('', $DEBUG). '</div>';
	$current = isset($desired) ? $desired->current(false) : FALSE;

	// Si local affichage de l'erreur, sinon envoi d'un mail
	if(!LOCAL and $current != 'admin')
	{
		if(!class_exists('smail')) include('cerberus/class/class.smail.php');
		$titre_email = config::get('sitename');
		$titre_email = $titre_email ? 'Cerberus - ' .$titre_email : 'CerberusDebug';
		
		$mailTitle = '[DEBUG] ' .f::filename($errorFile). '::' .$errorLine;
		$mail = new smail('maxime@stappler.fr', $mailTitle, $DEBUG);
		$mail->setExpediteur($titre_email, config::get('mail'));
		$mail->messageHTML();
		$mail->send();
	}
	else echo $DEBUG;
}
?>