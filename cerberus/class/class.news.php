<?php
class getNews
{
	/* 
	###############################
	VARIABLES PREPARATOIRES
	############################ */

	// Paramètres
	private $table = 'cerberus_news';
	private $page = 'news';
	
	// Affichage des news
	private $newsNumber = 5;
	private $newsPaginate = FALSE;
	private $newsOrder = 'date';
	private $newsStart;
	
	// Composantes
	private $displayThumb = TRUE;
	private $displayDate = TRUE;
	private $displayLink = TRUE;
	
	// Affichage de la miniature
	private $thumbWidth = 100;
	private $thumbHeight = 100;
	private $thumbCrop = TRUE;
	
	/*
	###############################
	FONCTIONS DE DEFINITION
	############################ */
		
	function __construct($page = 'news')
	{	
		if(!db::is_table('cerberus_news')) update::table('cerberus_news');
		$this->page = str_replace('&pageSub=', '-', $page);
		$this->multiWhere = (MULTILANGUE)
			? array('langue' => l::current())
			: NULL;
	}
	
	function setTable($table)
	{
		$this->table = $table;
	}
	
	function setDisplay($thumb, $date, $link)
	{
		$this->displayThumb = $thumb;
		$this->displayDate = $date;
		$this->displayLink = $link;
	}
	
	function sizeThumb($width, $height, $crop)
	{
		$this->thumbWidth = $width;
		$this->thumbHeight = $height;
		$this->thumbCrop = $crop;
	}
	
	function setNews($limit, $newsPaginate, $newsOrder)
	{
		$this->newsPaginate = $newsPaginate;
		$this->newsOrder = $newsOrder;
		
		if($this->newsPaginate)
		{
			$entries = db::count($this->table, $this->multiWhere);
			if($entries != 0)
			{
				pager::set($entries, 1, $limit);
				
				if(isset($_GET['pagenews']))
					pager::set($entries, $_GET['pagenews'], $limit);
			}
			else $this->newsPaginate = FALSE;
		}
	}
	function setTruncate($truncate, $mode)
	{
		if($truncate)
		{
			$this->displayLink = TRUE;
			$this->truncateNews = $mode;
		}
		else $this->truncateNews = FALSE;
	}
	
	/* 
	###############################
	AFFICHAGE DES NEWS
	############################ */

	function selectNews($id = NULL)
	{	
		// News seule ou toutes
		if(!empty($id))
		{
			$news = db::select(
				$this->table,
				'id, titre, date, contenu, path',
				array_merge(array('id' => $id), $this->multiWhere));
		}
		else
		{
			$limit = (!$this->newsPaginate)
				? pager::$limit
				: pager::db(). ',' .pager::$limit;
		
			$news = db::select(
				$this->table,
				'id, titre, date, contenu, path',
				$this->multiWhere,
				$this->newsOrder. ' DESC',
				NULL,
				pager::db(),
				pager::$limit);
		}		
		
		// Récupération des news
		if($news) foreach($news as $key => $value)
		{
			if(!empty($id)) $alt = 'wide';
			else $alt = (isset($alt) and $alt == 'alt') ? '' : 'alt';
		
			// Display
			$thisDate = ($this->displayDate)
				? '<br /><p class="date">' .$value['date']. '</p>'
				: NULL;

			if($this->displayThumb and !empty($value['path']) and file_exists('assets/file/news/' .$value['path']))
			{
			
				// Miniature
				$thisThumb =
					str::link(
						'assets/file/news/' .$value['path'], 
						str::img(
							timthumb('news/' .$value['path'], $this->thumbWidth, $this->thumbHeight, 1),
							$value['titre'],
							array('class' => 'float')),
						array('class' => 'colorbox'));
			}
			else $thisThumb = NULL;
				
			$thisLink = ($this->displayLink)
				? rewrite($this->page, array('actualite' => $value['id'], 'html' => $value['titre']))
				: '#' .$key;
			
			// News
			$contenu = $value['contenu'];
			if($this->truncateNews != FALSE and empty($id)) $contenu = truncate($contenu, $this->truncateNews[0], $this->truncateNews[1], ' [...]');
			$contenu = nl2br(bbcode(stripslashes($contenu)));
			if($this->displayLink and empty($id)) $contenu .= '<a href="' .$thisLink. '"><p class="readmore">' .l::get('news-more'). '</p></a>';
			
			echo '
			<div class="news ' .$alt. '" id="' .$key. '">
				<h2>' .str::link($thisLink, stripslashes($value['titre'])).$thisDate. '</h2>
				<div class="contenu">' .$thisThumb.$contenu. '</div>
				<p class="clear">&nbsp;</p>
			</div>';
		}
		else promptm('news-none', 'Aucune news à afficher');
		echo '<p class="clear"></p>';
	}
		
	// Liste des archives
	function selectArchives()
	{
		$news = db::select(
			$this->table,
			'id,
			date,
			DATE_FORMAT(date, "%Y-%m") AS mois,
			YEAR(date) AS year,
			MONTH(date) AS month,
			titre',
			$this->multiWhere,
			'date ASC');
		
		$actualDate = NULL;

		echo '<h1>' .l::get('news-archives'). '</h1>';
		if($news)
		{
			echo '<div class="news-archives">';
			foreach($news as $key => $value)
			{
				// Date actuelle
				if($value['mois'] != $actualDate)
				{
					if(!empty($actualDate)) echo '</ul></div>';
					echo '<div class="news-archives-month"><h2>' .l::month($value['date']). ' ' .$value['year']. '</h2><ul>';
					$actualDate = $value['mois'];
				}
				
				$titre = stripslashes($value['titre']);
				echo '<li>' .str::slink($this->page, $titre, array('actualite' => $value['id'], 'html' => $titre)). '</li>';
			}
			echo '</ul></div><p class="clear"></p></div>';
		}
		else promptm('news-none', 'Aucune news à afficher');
	}
	
	// Pagination
	function paginate()
	{			
		if($this->newsPaginate)
		{
			// Pagination
			echo '<div id="news-pagination">Pages - ';
			for($i = 1; $i <= pager::$pages; $i++)
			{
				$attributes = (!isset($_GET['actualite']) and $i == pager::get())
					? array('class' => 'hover')
					: NULL;
					
				echo str::slink($this->page, $i, array('pagenews' => $i), $attributes);
			}
			echo '</div>';
		}
	}
}
?>