<?php
class pager
{
	static public $entries;
	static public $page;
	static public $limit;
	static public $pages;

	// D�finit les variables environnement
	static function set($entries, $page, $limit)
	{
		self::$entries = $entries;
		self::$limit = $limit;
		self::$pages = ($entries > 0) ? ceil($entries / $limit) : 0;
		self::$page = self::sanitize($page, self::$pages);
	}

	// Page actuelle
	static function get()
	{
		return self::$page;
	}

	// Page suivante
	static function next()
	{
		return (self::$page+1 <= self::$pages) ? self::$page+1 : self::$page;
	}

	// Page pr�c�dente
	static function previous()
	{
		return (self::$page-1 >= 1) ? self::$page-1 : self::$page;
	}
	
	// Premi�re page
	static function first()
	{
		return 1;
	}

	// Derni�re page
	static function last()
	{
		return self::$pages;
	}

	// Page en cours est la premi�re
	static function is_first()
	{
		return (self::$page == 1) ? true : false;
	}

	// Page en cours est la derni�re
	static function is_last()
	{
		return (self::$page == self::$pages) ? true : false;
	}
	
	// Nombre de pages
	static function count()
	{
		return self::$pages;
	}

	// Valide la page demand�e
	static function sanitize($page, $pages)
	{
		if(!$pages) $pages = self::$pages;
		
		$page = intval($page);
		if($page > $pages) $page = $pages;
		if($page < 1) $page = 1;
		return $page;
	}

	// Retourne l'entr�e de d�but pour une requ�te
	static function db()
	{
		return (self::$page-1) * self::$limit;
	}
}
?>