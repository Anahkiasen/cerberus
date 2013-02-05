<?php
use Cerberus\Modules\Pager;

// Dependencies
use Cerberus\Core\Navigation;
use Cerberus\Toolkit\Content;

class PagerTest extends PHPUnit_Framework_TestCase
{
	private static $pager   = null;
	private static $entries = 20;
	private static $limit   = 5;
	private static $pages   = 0;

	// Data Providers ---------------------------------------------- /

	public function pageNumbers()
	{
		return array(
			array(-5),
			array(0),
			array(3),
			array(10),
			array(40)
		);
	}

	// Setup ------------------------------------------------------- /

	public static function setUpBeforeClass()
	{
		define('SQL', false);

		self::$pager = new Pager(self::$entries, self::$limit);
		self::$pages = self::$pager->count();
	}

	// Tests ------------------------------------------------------- /

	public function testConstruct()
	{
		$pager = new Pager(10, 5, 2, 'page');

		self::assertEquals($pager->entries, 10);
		self::assertEquals($pager->limit, 5);
		self::assertEquals($pager->page, 2);
		self::assertEquals($pager->getVar, 'page');
	}

	public function testAutoGetVar()
	{
		$pager = new Pager(10, 5, 2);

		self::assertEquals($pager->getVar, 'get_page');
	}

	public function testPageGetVar()
	{
		navigation::$page = 'test';
		$pager = new Pager(10, 5, 2);

		self::assertEquals($pager->getVar, 'test_page');
	}

	public function testNumberPages()
	{
		self::assertEquals(self::$pager->count(), 4);
	}

	public function testStartingPage()
	{
		self::assertEquals(self::$pager->page, 1);
	}

	/**
	 * @dataProvider pageNumbers
	 */
	public function testChangePage($newPage = 0)
	{
		self::$pager->change($newPage);
		$current = self::$pager->page;

		self::assertGreaterThanOrEqual(1, $current);
		self::assertLessThanOrEqual(self::$pages, $current);
	}

	public function testFirst()
	{
		self::$pager->change(self::$pager->first());
		self::assertEquals(self::$pager->page, 1);
	}

	public function testIsFirst()
	{
		$first = self::$pager->first();
		self::$pager->change($first);
		self::assertTrue(self::$pager->isFirst());
	}

	public function testLast()
	{
		$last = self::$pager->count();
		self::$pager->change($last);
		self::assertTrue(self::$pager->isLast());
	}

	/**
	 * @dataProvider pageNumbers
	 */
	public function testLimit($newPage = 0)
	{
		$limit = self::$pager->db($newPage);

		self::assertGreaterThanOrEqual(0, $limit);
		self::assertLessThanOrEqual(self::$pager->entries, $limit);
	}

	public function testRender()
	{
		content::start();
			self::$pager->pagination();
		$html = content::end(true);

		$numberPages = intval(self::$pager->count() + 2);
		$match = array(
			'tag'        => 'div',
			'attributes' => array('class' => 'pagination pagination-centered'),
			'child'      => array(
				'tag'      => 'ul',
				'children' => array(
					'count' => $numberPages,
					'only'  => array(
						'tag'   => 'li',
						'child' => array(
							'tag' => 'a'))))
			);
		self::assertTag($match, $html);
	}
}
