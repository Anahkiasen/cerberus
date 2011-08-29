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
	private $currentPage = 1;
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
		global $cerberus;
		$this->url = getURL(true);

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
	
	function setNews($newsNumber, $newsPaginate, $newsOrder)
	{
		$this->newsNumber = $newsNumber;
		$this->newsPaginate = $newsPaginate;
		$this->newsOrder = $newsOrder;
		
		if($this->newsPaginate == TRUE)
		{
			if(isset($_GET['pagenews'])) $this->currentPage = $_GET['pagenews'];
			$this->newsStart = ($this->currentPage - 1) * $this->newsNumber;
		}
	}
	function setTruncate($truncate, $mode)
	{
		if($truncate == true)
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

	function selectNews($id = '')
	{
		if(!empty($id))
		{
			$news= mysqlQuery('
			SELECT id, titre, date, contenu, path
			FROM ' .$this->table. '
			WHERE id=' .$id, TRUE);
		}
		else
		{
			$limit = ($this->newsPaginate == FALSE)
				? $this->newsNumber
				: $this->newsStart. ',' .$this->newsNumber;
		
			$news = mysqlQuery('
			SELECT id, titre, date, contenu, path
			FROM ' .$this->table. '
			ORDER BY ' .$this->newsOrder. ' DESC
			LIMIT ' .$limit,
			TRUE);
		}		
		
		// Récupération des news
		foreach($news as $key => $value)
		{
			if(!empty($id)) $alt = 'wide';
			else $alt = (isset($alt) and $alt == 'alt') ? '' : 'alt';
		
			// Display
			$thisDate = ($this->displayDate == TRUE)
				? '<br /><p class="date">' .$value['date']. '</p>'
				: NULL;
			$thisThumb = ($this->displayThumb == TRUE and !empty($value['path']) and file_exists('file/news/' .$value['path']))
				? '<a class="colorbox" href="file/news/' .$value['path']. '">
				<img src="' .timthumb('news/' .$value['path'], $this->thumbWidth, $this->thumbHeight, 1, false). '" class="float" />
				</a>'
				: NULL;
				
			$thisLink = ($this->displayLink == TRUE)
				? rewrite($this->page, array('actualite' => $key, 'html' => $value['titre']))
				: '#' .$key;
			
			// News
			$contenu = $value['contenu'];
			if($this->truncateNews != FALSE and empty($id)) $contenu = truncate($contenu, $this->truncateNews[0], $this->truncateNews[1], ' [...]');
			$contenu = nl2br(bbcode(html($contenu)));
			if($this->displayLink == TRUE and empty($id)) $contenu .= '<a href="' .$thisLink. '"><p class="readmore">Lire la suite</p></a>';
			
			echo '
			<div class="news ' .$alt. '" id="' .$key. '">
				<h2><a href="' .$thisLink. '">' .html($value['titre']). '</a>' .$thisDate. '</h2>
				<div class="contenu">' .$thisThumb.$contenu. '</div>
				<p class="clear">&nbsp;</p>
			</div>';
		}
		echo '<p class="clear"></p>';
	}
		
	// Liste des archives
	function selectArchives()
	{
		$startingPage = 1;
		$newsCounter = 0;
		
		$nomsMois = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'décembre');
	
		$news = mysqlQuery('
		SELECT id, date, DATE_FORMAT(date, "%Y-%m") AS mois, YEAR(date) AS year, MONTH(date) AS month, titre
		FROM ' .$this->table. '
		ORDER BY date ASC', 
		TRUE);
		
		$actualDate = NULL;

		echo '<div class="news-archives"><h1>Archives par mois</h1>';
		foreach($news as $key => $value)
		{
			// Date actuelle
			if($value['mois'] != $actualDate)
			{
				if($actualDate != '') echo '</ul></div>';
				echo '<div class="news-archives-month"><h2>' .$nomsMois[$value['month']-1]. ' ' .$value['year']. '</h2><ul>';
				$actualDate = $value['mois'];
			}
			
			$newsCounter++;
			if($newsCounter == $this->newsNumber)
			{
				$startingPage++;
				$newsCounter = 0;
			}
			$titre = stripslashes($value['titre']);
			echo '<li><a href="' .rewrite($this->page, array('actualite' => $key, 'html' => $titre)). '">' .$titre. '</a></li>';
		}
		echo '</ul></div><p class="clear"></p></div>';
	}
	
	// Pagination
	function paginate()
	{
		$nombreNews = mysqlQuery('SELECT COUNT(id) FROM ' .$this->table);
		$nombrePages = ceil($nombreNews / $this->newsNumber);
				
		// Pagination
		echo '<div id="news-pagination">Pages - ';
		for($i = 1; $i <= $nombrePages; $i++)
		{
			$classHover = (!isset($_GET['actualite']) and $i == $this->currentPage) ? 'class="hover"' : '';	
			echo '<a href="' .rewrite($this->page, array('pagenews' => $i)). '" ' .$classHover. '>' .$i. '</a>';
		}
		echo '</div>';
	}
	
	/* 
	###############################
	ADMIN DES NEWS
	############################ */
	
	function adminNews()
	{
		$newsAdmin = new AdminClass();
		$newsAdmin->setPage('news');
		$newsAdmin->createList(array('titre', 'date'));
		
		// Formulaire
		if(isset($_GET['add']) || isset($_GET['edit']))
		{	
			// Paramètres ajout/modif
			if(isset($_GET['edit']))
			{
				$diffText = 'Modifier';
				$urlAction = 'edit=' .$_GET['edit'];
				$path = mysqlQuery('SELECT path FROM news WHERE id=' .$_GET['edit']);			
			}
			else
			{
				$diffText = 'Ajouter';
				$urlAction = 'add';
				$path = 'null';
			}
	
			$form = new form(false, array('action' => $this->url. '?page=admin&admin=news&' .$urlAction));
			$form->getValues($newsAdmin->getFieldsTable());
			
			$form->openFieldset($diffText. ' une news');
				$form->addText('titre', 'Titre de la news');
				$form->addTextarea('contenu', 'Texte de la news');
				if(file_exists('file/news/' .$path)) $form->insertText('
					<dl class="actualThumb">
					<dt>Supprimer la miniature actuelle</dt>
					<dd style="text-align:center"><p><img src="' .timthumb('news/' .$path, 125, 125, 1, false). '" /><br />
					<a href="' .$this->url. '?page=admin&admin=' .$this->page. '&edit=' .$_GET['edit']. '&deleteThumb=' .$_GET['edit']. '">Supprimer</a></p></dd></dl>');
				$form->addFile('thumb', 'Envoi d\'une miniature');
				$form->addEdit();
				if($diffText == 'Ajouter') $form->addHidden('date', date('Y-m-d')); 
				$form->addSubmit($diffText);
			$form->closeFieldset();
			
			echo $form;
		}
	}
}
?>