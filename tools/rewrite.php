<?php
function rewrite($page, $params = '')
{
	global $rewriteMode;
	global $meta;
	global $navigation;
	
	if(!is_array($page)) $page = explode('-', $page);
	
	$page0 = $page[0];
	$page1 = (isset($page[1])) ? $page[1] : $navigation[$page0][0];
	
	if(isset($params['html']))
	{
		$thisHTML = $params['html'];
		unset($params['html']);
	}	
	
	if($rewriteMode == false or $_SERVER['HTTP_HOST'] == 'localhost:8888')
	{
		
		$lien = 'index.php?page=' .$page0;
		if($page1) $lien .= '&pageSub=' .$page1;
		if(!empty($params))
		{
			if(is_array($params)) $lien .= '&' .simplode('=', '&', $params);
			else $lien .= '&' .$params;
		}
	}
	else
	{
		$lien = $page0. '/';
		if($page1) $lien .= $page1. '/';
	
		if(!empty($params))
		{
			if(is_array($params)) $lien .= simplode('-', '/', $params);
			else $lien .= $params;
			$lien .= '/';
		}
		$lien = str_replace($page0. '-', '', $lien);
				
				
		$thisPage = $page0. '-' .$page1;
		if(isset($meta[$thisPage]) and !isset($thisHTML)) $thisHTML = (!empty($meta[$thisPage]['url'])) ? $meta[$thisPage]['url'] : $meta[$thisPage]['titre'];
		if(isset($thisHTML))
		{
			$lien .= normalize($thisHTML, true);
			$lien .= '.html';
		}
	}
	return $lien;
}
?>