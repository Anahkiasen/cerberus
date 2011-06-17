<?php
function getURL($truncate = false)
{
	if($truncate == false)
	{
		$pageURL = 'http';
		if(isset($_SESSION['HTTPS']) && $_SERVER['HTTPS'] == 'on') $pageURL .= "s";
		$pageURL .= "://";
		$pageURL .= ($_SERVER["SERVER_PORT"] != "80")
			? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]
			: $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		return $pageURL;
	}
	else
	{
		$pageName = explode('/', $_SERVER['REQUEST_URI']);
		$pageName = $pageName[2];
		return $pageName;
	}
}
?>