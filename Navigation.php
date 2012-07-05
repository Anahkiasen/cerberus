<?php
namespace Cerberus;

use Cerberus\Toolkit\String;

class Navigation
{
	public static function render($navigation, $type = 'pills')
	{
		$newNavigation = array();
		foreach($navigation as $text => $route)
		{
			$route = String::find('@', $route) ? action($route) : url($route);
			$link = array('label' => $text, 'url' => $route);
			if(\URL::to($route) == \URL::current()) $link['active'] = true;
			$newNavigation[] = $link;
		}
		return call_user_func(array('\Bootstrapper\Navigation', $type), $newNavigation);
	}
}