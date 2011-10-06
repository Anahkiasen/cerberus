<?php
/*
	Fonction browserSelector
	# Crée une chaine contenant la définition exacte de l'user-agent de l'internaute
*/
function browserSelector(&$renderAgent)
{
	$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
	
	$gecko = 'gecko';
	$webkit = 'webkit';
	$safari = 'safari';
	$return = array();
	
	// Navigateur
	if(!preg_match('/opera|webtv/i', $userAgent) && preg_match('/msie\s(\d)/', $userAgent, $array)) $return[] = 'ie ie' .$array[1];
	else if(strstr($userAgent, 'firefox/2')) $return[] = $gecko. ' ff2';
	else if(strstr($userAgent, 'firefox/3.5')) $return[] = $gecko. ' ff3 ff3_5';
	else if(strstr($userAgent, 'firefox/3')) $return[] = $gecko. ' ff3';
	else if(strstr($userAgent, 'gecko/')) $return[] = $gecko;
	else if(preg_match('/opera(\s|\/)(\d+)/', $userAgent, $array)) $return[] = 'opera opera' .$array[2];
	else if(strstr($userAgent, 'konqueror')) $return[] = 'konqueror';
	else if(strstr($userAgent, 'chrome')) $return[] = $webkit. ' ' .$safari. ' chrome';
	else if(strstr($userAgent, 'iron')) $return[] = $webkit. ' ' .$safari. ' iron';
	else if(strstr($userAgent, 'applewebkit/')) $return[] = (preg_match('/version\/(\d+)/i', $userAgent, $array)) ? $webkit. ' ' .$safari. ' ' .$safari .$array[1] : $webkit. ' ' .$safari;
	else if(strstr($userAgent, 'mozilla/')) $return[] = $gecko;
	
	// platform
	if(strstr($userAgent, 'j2me')) $return[] = 'mobile';
	else if(strstr($userAgent, 'iphone')) $return[] = 'iphone';
	else if(strstr($userAgent, 'ipod')) $return[] = 'ipod';
	else if(strstr($userAgent, 'mac')) $return[] = 'mac';
	else if(strstr($userAgent, 'darwin')) $return[] = 'mac';
	else if(strstr($userAgent, 'webtv')) $return[] = 'webtv';
	else if(strstr($userAgent, 'win')) $return[] = 'win';
	else if(strstr($userAgent, 'freebsd')) $return[] = 'freebsd';
	else if(strstr($userAgent, 'x11') || strstr($userAgent, 'linux')) $return[] = 'linux';
	
	$renderAgent = join(' ', $return);
}
?>