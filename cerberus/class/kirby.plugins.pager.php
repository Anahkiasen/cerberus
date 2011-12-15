<?php
class pager
{
	static public $entries;
	static public $page;
	static public $limit;
	static public $pages;

	// Définit les variables environnement
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

	// Page précédente
	static function previous()
	{
		return (self::$page-1 >= 1) ? self::$page-1 : self::$page;
	}
	
	// Première page
	static function first()
	{
		return 1;
	}

	// Dernière page
	static function last()
	{
		return self::$pages;
	}

	// Page en cours est la première
	static function is_first()
	{
		return (self::$page == 1) ? true : false;
	}

	// Page en cours est la dernière
	static function is_last()
	{
		return (self::$page == self::$pages) ? true : false;
	}
	
	// Nombre de pages
	static function count()
	{
		return self::$pages;
	}

	// Valide la page demandée
	static function sanitize($page, $pages)
	{
		if(!$pages) $pages = self::$pages;
		
		$page = intval($page);
		if($page > $pages) $page = $pages;
		if($page < 1) $page = 1;
		return $page;
	}

	// Retourne l'entrée de début pour une requête
	static function db()
	{
		return (self::$page-1) * self::$limit;
	}
}
?>