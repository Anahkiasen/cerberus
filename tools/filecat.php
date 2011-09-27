<?php
function filecat($extension) 
{
	$typeArray = array(
		'audio' 		=> array('aac', 'ac3', 'aif', 'aiff', 'm3a', 'm4a', 'm4b', 'mka', 'mp1', 'mp2', 'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma'),
		'video' 		=> array('asf', 'avi', 'divx', 'dv', 'flv', 'm4v', 'mkv', 'mov', 'mp4', 'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt', 'rm', 'vob', 'wmv'),
		'document' 		=> array('doc', 'docx', 'docm', 'dotm', 'odt', 'pages', 'pdf', 'rtf', 'wp', 'wpd'),
		'spreadsheet' 	=> array('numbers', 'ods', 'xls', 'xlsx', 'xlsb', 'xlsm' ),
		'interactive' 	=> array('key', 'ppt', 'pptx', 'pptm', 'odp', 'swf'),
		'text' 			=> array('asc', 'csv', 'tsv', 'txt'),
		'archive' 		=> array('bz2', 'cab', 'dmg', 'gz', 'rar', 'sea', 'sit', 'sqx', 'tar', 'tgz', 'zip'),
		'code' 			=> array('css', 'htm', 'html', 'php', 'js'),
		'image'			=> array('jpeg', 'jpg', 'png', 'gif'));
		
	foreach($typeArray as $type => $exts)
		if(in_array($extension, $exts)) return $type;
}
?>