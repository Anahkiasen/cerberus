<?php
function countryFromIP($possible = array('fr', 'de'), $default = 'fr')
{
	$ipDetail = array();
	$ipAddr = $_SERVER['REMOTE_ADDR'];
	
	if($ipAddr == "::1") $ipDetail['country_code'] = $default;
	else
	{
		ip2long($ipAddr) == -1 || ip2long($ipAddr) === false ? trigger_error('[ERREUR IP]', E_USER_ERROR) : '';
		$xml = file_get_contents('http://api.hostip.info/?ip=' .$ipAddr);
		preg_match("@<Hostip>(\s)*<gml:name>(.*?)</gml:name>@si", $xml, $match);
		preg_match("@<countryName>(.*?)</countryName>@si", $xml, $matches);
		preg_match("@<countryAbbrev>(.*?)</countryAbbrev>@si", $xml, $cc_match);
		$ipDetail['city'] = $match[2];
		$ipDetail['country'] = $matches[1];
		$ipDetail['country_code'] = strtolower($cc_match[1]);
	}
	if(!in_array($ipDetail['country_code'], $possible)) $ipDetail['country_code'] = $default;
	
	return $ipDetail;
}
?>