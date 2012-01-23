<?php
class browser
{
	static public $ua = false;
	static public $name = false;
	static public $engine = false;
	static public $version = false;
	static public $platform = false;
	static public $mobile = false;
	static public $ios = false;
	static public $iphone = false;
	
	// Fonctions de détection
	static function name($ua = NULL)
	{
		self::detect($ua);
		return self::$name;
	}

	static function engine($ua = NULL)
	{
		self::detect($ua);
		return self::$engine;
	}

	static function version($ua = NULL)
	{
		self::detect($ua);
		return self::$version;
	}

	static function platform($ua = NULL)
	{
		self::detect($ua);
		return self::$platform;
	}

	static function mobile($ua = NULL)
	{
		self::detect($ua);
		return self::$mobile;
	}

	static function iphone($ua = NULL)
	{
		self::detect($ua);
		return self::$iphone;
	}

	static function ios($ua = NULL)
	{
		self::detect($ua);
		return self::$ios;
	}

	static function css($ua = NULL, $array = false)
	{
		self::detect($ua);
		$css[] = self::$engine;
		$css[] = self::$name;
		if(self::$version) $css[] = self::$name . str_replace('.', '_', self::$version);
		$css[] = self::$platform;
		return ($array) ? $css : implode(' ', $css);
	}

	// FONCTION COEUR
	static function detect($ua = NULL)
	{
		$ua = ($ua) ? str::lower($ua) : str::lower(server::get('http_user_agent'));

		// On ne fait la détection qu'une seule fois
		if(self::$ua == $ua)
			return array
			(
				'name'		 => self::$name,
				'engine'	 => self::$engine,
				'version'	=> self::$version,
				'platform' => self::$platform,
				'agent'		=> self::$ua,
				'mobile'	 => self::$mobile,
				'iphone'	 => self::$iphone,
				'ios'			=> self::$ios,
			);

		self::$ua		= $ua;
		self::$name		= false;
		self::$engine	= false;
		self::$version	= false;
		self::$platform = false;

		// NAVIGATEUR
		if(!preg_match('/opera|webtv/i', self::$ua) && preg_match('/msie\s(\d)/', self::$ua, $array))
		{
			self::$version = $array[1];
			self::$name = 'ie';
			self::$engine	= 'trident';
		}
		else if(strstr(self::$ua, 'firefox/3.6'))
		{
			self::$version = 3.6;
			self::$name = 'fx';
			self::$engine	= 'gecko';
		}	
		else if (strstr(self::$ua, 'firefox/3.5'))
		{
			self::$version = 3.5;
			self::$name = 'fx';
			self::$engine	= 'gecko';
		}	
		else if(preg_match('/firefox\/(\d+)/i', self::$ua, $array))
		{
			self::$version = $array[1];
			self::$name = 'fx';
			self::$engine	= 'gecko';
		}
		else if(preg_match('/opera(\s|\/)(\d+)/', self::$ua, $array))
		{
			self::$engine	= 'presto';
			self::$name = 'opera';
			self::$version = $array[2];
		}
		else if(strstr(self::$ua, 'konqueror'))
		{
			self::$name = 'konqueror';
			self::$engine	= 'webkit';
		}
		else if(strstr(self::$ua, 'iron'))
		{
			self::$name = 'iron';
			self::$engine	= 'webkit';
		}
		else if(strstr(self::$ua, 'chrome'))
		{
			self::$name = 'chrome';
			self::$engine	= 'webkit';
			if(preg_match('/chrome\/(\d+)/i', self::$ua, $array)) self::$version = $array[1];
		}
		else if(strstr(self::$ua, 'applewebkit/'))
		{
			self::$name = 'safari';
			self::$engine	= 'webkit';
			if(preg_match('/version\/(\d+)/i', self::$ua, $array)) self::$version = $array[1];
		}
		else if(strstr(self::$ua, 'mozilla/'))
		{
			self::$engine	= 'gecko';
			self::$name = 'fx';
		}

		// PLATEFORME
		if(strstr(self::$ua, 'j2me')) self::$platform = 'mobile';
		else if(strstr(self::$ua, 'iphone')) self::$platform = 'iphone';
		else if(strstr(self::$ua, 'ipod')) self::$platform = 'ipod';
		else if(strstr(self::$ua, 'ipad')) self::$platform = 'ipad';
		else if(strstr(self::$ua, 'mac')) self::$platform = 'mac';
		else if(strstr(self::$ua, 'darwin')) self::$platform = 'mac';
		else if(strstr(self::$ua, 'webtv')) self::$platform = 'webtv';
		else if(strstr(self::$ua, 'win')) self::$platform = 'win';
		else if(strstr(self::$ua, 'freebsd')) self::$platform = 'freebsd';
		else if(strstr(self::$ua, 'x11') || strstr(self::$ua, 'linux')) self::$platform = 'linux';

		self::$mobile = (self::$platform == 'mobile') ? true : false;
		self::$iphone = (in_array(self::$platform, array('ipod', 'iphone'))) ? true : false;
		self::$ios		= (in_array(self::$platform, array('ipod', 'iphone', 'ipad'))) ? true : false;

		return array
		(
			'name'		 => self::$name,
			'engine'	 => self::$engine,
			'version'	=> self::$version,
			'platform' => self::$platform,
			'agent'		=> self::$ua,
			'mobile'	 => self::$mobile,
			'iphone'	 => self::$iphone,
			'ios'			=> self::$ios,
		);
	}
}
?>