<?php
class getNews
{
	/* 
	###############################
	VARIABLES PREPARATOIRES
	############################ */

	// Paramètres
	private $table = 'news';
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
		$this->page = str_replace('&pageSub=', '-', $page);
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
			$entries = db::count($this->table);
			pager::set($entries, 1, $limit);
			
			if(isset($_GET['pagenews']))
				pager::set($entries, $_GET['pagenews'], $limit);
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
		if(!empty($id))
		{
			$news = db::select(
				$this->table,
				'id, titre, date, contenu, path',
				array('id' => $id));
		}
		else
		{
			$limit = (!$this->newsPaginate)
				? pager::$limit
				: pager::db(). ',' .pager::$limit;
		
			$news = db::query('
				SELECT id, titre, date, contenu, path
				FROM ' .$this->table. '
				ORDER BY ' .$this->newsOrder. ' DESC
				LIMIT ' .$limit);
		}		
		
		// Récupération des news
		foreach($news as $key => $value)
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
							timthumb('news/' .$value['path'], $this->thumbWidth, $this->thumbHeight, 1, false),
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
			$contenu = nl2br(bbcode(html($contenu)));
			if($this->displayLink and empty($id)) $contenu .= '<a href="' .$thisLink. '"><p class="readmore">Lire la suite</p></a>';
			
			echo '
			<div class="news ' .$alt. '" id="' .$key. '">
				<h2>' .str::link($thisLink, html($value['titre'])).$thisDate. '</h2>
				<div class="contenu">' .$thisThumb.$contenu. '</div>
				<p class="clear">&nbsp;</p>
			</div>';
		}
		echo '<p class="clear"></p>';
	}
		
	// Liste des archives
	function selectArchives()
	{
		$nomsMois = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'décembre');
	
		$news = 
			db::query('
			SELECT
				id,
				date,
				DATE_FORMAT(date, "%Y-%m") AS mois,
				YEAR(date) AS year,
				MONTH(date) AS month,
				titre
			FROM ' .$this->table. '
			ORDER BY date ASC');
		
		$actualDate = NULL;

		echo '<div class="news-archives"><h1>Archives par mois</h1>';
		foreach($news as $key => $value)
		{
			// Date actuelle
			if($value['mois'] != $actualDate)
			{
				if(!empty($actualDate)) echo '</ul></div>';
				echo '<div class="news-archives-month"><h2>' .$nomsMois[$value['month']-1]. ' ' .$value['year']. '</h2><ul>';
				$actualDate = $value['mois'];
			}
			
			$titre = stripslashes($value['titre']);
			echo '<li>' .str::slink($this->page, array('actualite' => $value['id'], 'html' => $titre), $titre). '</li>';
		}
		echo '</ul></div><p class="clear"></p></div>';
	}
	
	// Pagination
	function paginate()
	{			
		// Pagination
		echo '<div id="news-pagination">Pages - ';
		for($i = 1; $i <= pager::$pages; $i++)
		{
			$attributes = (!isset($_GET['actualite']) and $i == pager::get())
				? array('class' => 'hover')
				: NULL;
				
			echo str::slink($this->page, array('pagenews' => $i), $i, $attributes);
		}
		echo '</div>';
	}
	
	/* 
	###############################
	ADMIN DES NEWS
	############################ */
	
	function adminNews()
	{	
		$newsAdmin = new AdminPage();
		$newsAdmin->setPage('news');
		$newsAdmin->createList(array('titre', 'date'));
		$newsAdmin->addOrEdit($diff, $diffText, $urlAction);

		// Formulaire
		$form = new form(false, array('action' => rewrite('admin-news', $urlAction)));
		$form->getValues($newsAdmin->getFieldsTable());
		
		$form->openFieldset($diffText. ' une news');
			$form->addText('titre', 'Titre de la news');
			$form->addTextarea('contenu', 'Texte de la news');
			if(isset($_GET['edit_news']))
			{
				$path = $newsAdmin->getImage(get('edit_news'));
				if(file_exists('assets/file/news/' .$path)) $form->insertText('
					<dl class="actualThumb">
					<dt>Supprimer la miniature actuelle</dt>
					<dd style="text-align:center"><p><img src="' .timthumb('news/' .$path, 125, 125, 1, false). '" /><br />
					' .str::slink('admin-' .$this->page, array('edit_news' => $_GET['edit_news'], 'deleteThumb' => $_GET['edit_news']), 'Supprimer'). '</p></dd></dl>');
			}
			$form->addFile('thumb', 'Envoi d\'une miniature');
			$form->addEdit();
			if($diffText == 'Ajouter') $form->addHidden('date', date('Y-m-d')); 
			$form->addSubmit($diffText);
		$form->closeFieldset();
			
		echo $newsAdmin->formAddOrEdit($form);
	}
}
?>