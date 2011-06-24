<?php
function desiredPage($navigation, $subnav = FALSE)
{
	$thisPage = (isset($_GET['page']) && isset($navigation[$_GET['page']])) ? $_GET['page'] : 'home';
	
	if(($subnav == TRUE) or isset($_GET['subpage']))
	{
		$subPage = $navigation[$thisPage];
		if(is_array($subPage)) $subPage = $subPage[0];
		if(isset($_GET['subpage']) && in_array($_GET['subpage'], $navigation[$thisPage])) $subPage = $_GET['subpage'];
		$subPageString = '-' .$subPage;
	}
	else $subPage = $subPageString = '';
	
	if(file_exists('pages/' .$thisPage.$subPageString. '.html')) $thisExtension = '.html';
	elseif(file_exists('pages/' .$thisPage.$subPageString. '.php')) $thisExtension = '.php';
	else 
	{
		$thisPage = 'home';
		$thisExtension = '.php';
	}
	return array($thisPage, $subPage, $thisExtension, '' .$thisPage.$subPageString.$thisExtension);
}
?>
