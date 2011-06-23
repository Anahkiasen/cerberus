<?php
function checkMail($email)
{
	if(!empty($email) and preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email)) return true;
	else return false;
}
?>