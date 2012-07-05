<?php
namespace Cerberus\Toolkit;

use Cerberus\Toolkit\String;

class URL extends \Laravel\URL
{
	public static function currentRoute()
	{
		$current = String::remove(URL::base(), URL::current());
		if($current != '/') $current = substr($current, 1);

		return $current;
	}
}