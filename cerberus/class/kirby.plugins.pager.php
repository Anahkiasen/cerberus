<?php
class pager
{
	static public $entries;
	static public $page;
	static public $limit;
	static public $pages;

	static private $pagination;
	static private $get_var;

	/* Définit les variables environnement */
	public static function set($entries, $page = null, $limit, $get_var = null)
	{
		self::$get_var = !$get_var ? navigation::current(). '_page' : $get_var;
		if(!$page) $page = a::get($_GET, self::$get_var, 1);

		self::$pagination = null;
		self::$entries = $entries;
		self::$limit = $limit;
		self::$pages = ($entries > 0) ? ceil($entries / $limit) : 0;
		self::$page = self::sanitize($page, self::$pages);
	}

	/* Page actuelle */
	public static function get()
	{
		return self::$page;
	}

	/* Page suivante */
	public static function next()
	{
		return (self::$page+1 <= self::$pages) ? self::$page+1 : self::$page;
	}

	/* Page précédente */
	public static function previous()
	{
		return (self::$page-1 >= 1) ? self::$page-1 : self::$page;
	}

	/* Première page */
	public static function first()
	{
		return 1;
	}

	/* Dernière page */
	public static function last()
	{
		return self::$pages;
	}

	/* Page en cours est la première */
	public static function is_first()
	{
		return (self::$page == 1) ? true : false;
	}

	/* Page en cours est la derniére */
	public static function is_last()
	{
		return (self::$page == self::$pages) ? true : false;
	}

	/* Nombre de pages */
	public static function count()
	{
		return self::$pages;
	}

	/* Valide la page demandée */
	public static function sanitize($page, $pages)
	{
		if(!$pages) $pages = self::$pages;

		$page = intval($page);
		if($page > $pages) $page = $pages;
		if($page < 1) $page = 1;
		return $page;
	}

	/* Retourne l'entrée de début pour une requête */
	public static function db()
	{
		return (self::$page-1) * self::$limit;
	}

	/* Builds a navigation */
	public static function pagination()
	{
		if(!empty(self::$pagination)) echo self::$pagination;
		else
		{
			content::start();
			?>
			<div class="pagination pagination-centered">
				<ul>
					<?php
					echo '<li><a href="' .url::reload(array(self::$get_var => self::previous())). '">&laquo;</a></li>';

					for($i = self::first(); $i <= self::last(); $i++)
					{
						$class = ($i == self::get()) ? ' class="active"' : null;
						echo '<li' .$class. '>' .str::link(url::reload(array(self::$get_var => $i)), $i). '</li>';
					}

					echo '<li><a href="' .url::reload(array(self::$get_var => self::next())). '">&raquo;</a></li>';
					?>
				</ul>
			</div>
			<?php
			self::$pagination = content::end(true);
			echo self::$pagination;
		}
	}
}
