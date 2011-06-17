<?php
function checkMail($mail)
{
	if(preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $mail)) return true;
	else return false;
}
?>