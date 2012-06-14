<?php
class Pager
{
	/**
	 * Number of entries
	 * @var integer
	 */
	public $entries     = 0;

	/**
	 * Current page
	 * @var integer
	 */
	public $page        = 0;

	/**
	 * Number of entries to display per page
	 * @var integer
	 */
	public $limit       = 0;

	/**
	 * Total number of pages
	 * @var integer
	 */
	private $pages       = 0;

	/**
	 * HTML rendering of the pagination
	 * @var string
	 */
	private $pagination = null;

	/**
	 * Name of the GET variable used to change page
	 * @var string
	 */
	public $getVar    = null;

	// Setup ------------------------------------------------------- /

	/**
	 * Setup Pager environnement
	 *
	 * @param int    $entries A number of entries
	 * @param int    $limit   Number of entries per page
	 * @param int    $page    Number of the current page
	 * @param string $getVar A facultative GET var to use to change page
	 */
	public function __construct($entries, $limit, $page = null, $getVar = null)
	{
		// If no GET variable is given, defaults to {page}_page
		if(!$getVar)
		{
			$current = navigation::current();
			$getVar  = $current ? $current.'_page' : 'get_page';
		}
		$this->getVar =  $getVar;

		// Get the page number
		if(!$page) $page = r::get($this->getVar, 1);

		// Set all variables
		$this->entries  = $entries;
		$this->limit    = $limit;
		$this->pages    = ($entries > 0) ? ceil($entries / $limit) : 0;
		$this->page     = $this->sanitize($page, $this->pages);
	}

	/**
	 * Change page
	 *
	 * @param  int     $page The new page
	 * @return boolean Whether the page changed or not
	 */
	public function change($page)
	{
		$currentPage = $this->get();
		if($page == $currentPage) return true;

		$this->page = self::sanitize($page);
		return $this->page != $currentPage;
	}

	// Common API -------------------------------------------------- /

	/**
	 * Get the current page
	 *
	 * @return int The current page
	 */
	public function get()
	{
		return $this->page;
	}

	/**
	 * Get the next page
	 *
	 * @return int The next page
	 */
	public function next()
	{
		$next = $this->page + 1;
		return ($next <= $this->pages) ? $next : $this->page;
	}

	/**
	 * Get the previous page
	 *
	 * @return int The previous page
	 */
	public function previous()
	{
		$previous = $this->page - 1;
		return ($previous >= 1) ? $previous : $this->page;
	}

	/**
	 * Get the first page
	 *
	 * @return int The first page
	 */
	public function first()
	{
		return 1;
	}

	/**
	 * Get the last page
	 *
	 * @return int the last page
	 */
	public function last()
	{
		return $this->pages;
	}

	/**
	 * Whether the current page is the first one
	 *
	 * @return boolean First page or not
	 */
	public function isFirst()
	{
		return ($this->page == 1);
	}

	/**
	 * Whether the current page is the last one
	 *
	 * @return boolean Last page or not
	 */
	public function isLast()
	{
		return ($this->page == $this->pages);
	}

	/**
	 * Current number of pages
	 *
	 * @return int The number of pages
	 */
	public function count()
	{
		return $this->pages;
	}

	/**
	 * Gets the first entry's ID for a given page (for LIMIT queries)
	 *
	 * @param  int $page A page number
	 * @return int A starting number
	 */
	public function db($page = null)
	{
		if(!$page) $page = $this->get();

		return self::sanitize($page - 1) * $this->limit;
	}

	/**
	 * Builds an HTML pagination with current informations
	 *
	 * @return string An HTML pagination
	 */
	public function pagination()
	{
		?>
		<div class="pagination pagination-centered">
			<ul>
				<?php
				$previous = str::slink(null, '&laquo;', array($this->getVar => $this->previous()));
				echo '<li>' .$previous. '</li>';

				for($i = $this->first(); $i <= $this->last(); $i++)
				{
					$class = ($i == $this->get()) ? ' class="active"' : null;
					echo '<li' .$class. '>' .str::slink(null, $i, array($this->getVar => $i)). '</li>';
				}

				$next = str::slink(null, '&raquo;', array($this->getVar => $this->next()));
				echo '<li>' .$next. '</li>';
				?>
			</ul>
		</div>
		<?php
	}

	// Toolkit ----------------------------------------------------- /

	/**
	 * Makes sure a page number is within the defined limits
	 *
	 * @param  int $page  A page number
	 * @param  int $pages A number of pages
	 * @return int        A sanitized page number
	 */
	public function sanitize($page, $pages = null)
	{
		// If no total number of pages, use current one
		if(!$pages) $pages = $this->pages;

		// Correct page number if under or above limits
		$page = intval($page);
		if($page > $pages) $page = $pages;
		if($page < 1) $page = 1;

		return $page;
	}
}
