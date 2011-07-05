<?php
function checkPhone($phone)
{
	if(!empty($phone) and preg_match("#^0[1-78]([-. ]?[0-9]{2}){4}$#", $phone)) return true;
	else return false;
}
?>