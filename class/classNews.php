<?php
class getNews
{
	/* 
	###############################
	VARIABLES PREPARATOIRES
	############################ */

	// Paramètres
	private $table = 'news';
	
	// Affichage des news
	private $newsNumber = 5;
	private $newsPaginate = FALSE;
	private $newsOrder = 'date';
	
	// Composantes
	private $displayThumb = TRUE;
	private $displayDate = TRUE;
	
	// Affichage de la miniature
	private $thumbWidth = 100;
	private $thumbHeight = 100;
	private $thumbCrop = TRUE;
	
	/*
	###############################
	FONCTIONS DE DEFINITION
	############################ */
		
	function setTable($table)
	{
		$this->table = $table;
	}
	
	function setDisplay($thumb, $date)
	{
		$this->displayThumb = $thumb;
		$this->displayDate = $date;
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
	}
	
	/* 
	###############################
	AFFICHAGE DES NEWS
	############################ */

	function selectNews()
	{
		// Requête
		$news = mysqlQuery('
		SELECT id, titre, date, contenu
		FROM ' .$this->table. '
		ORDER BY ' .$this->newsOrder. ' DESC
		LIMIT ' .$this->newsNumber,
		'id', TRUE);
		
		// Récupération des news
		foreach($news as $key => $value)
		{
			if(isset($alt) and $alt == 'alt') $alt = '';
			else $alt = 'alt';
		
			// Display
			if($this->displayDate == TRUE) $thisDate = '<br ><p class="date">' .$value['date']. '</p>';
			else $thisDate = NULL;
			
			if($this->displayThumb == TRUE) $thisThumb = '<a class="colorbox" href="file/news/' .$key. '.jpg">
			<img src="file/timthumb.php?src=file/news/' .$key. '.jpg&h=' .$this->thumbHeight. '&w=' .$this->thumbWidth. '&zc=' .$this->thumbCrop. '" class="float" /></a>';
			else $thisThumb = NULL;
			
			// News
			echo '
			<div class="news ' .$alt. '" id="' .$key. '">
				<h2>' .html($value['titre']).$thisDate. '</h2>
				<p class="contenu">' .$thisThumb.nl2br(html($value['contenu'])). '</p>
				<p class="clear"></p>
			</div>';
		}
	}
	
	// Liste des archives
	function selectArchives()
	{
		$nomsMois = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'décembre');
	
		$news = mysqlQuery('
		SELECT id, date, DATE_FORMAT(date, "%Y-%m") AS mois, YEAR(date) AS year, MONTH(date) AS month, titre
		FROM ' .$this->table. '
		ORDER BY date DESC', 'id', TRUE);
		
		$actualDate = NULL;

		echo '<div class="news-archives">';
		foreach($news as $key => $value)
		{
			// Date actuelle
			if($value['mois'] != $actualDate)
			{
				if($actualDate != '') echo '</ul></div>';
				echo '<div class="news-archives-month"><h2>' .$nomsMois[$value['month']-1]. ' ' .$value['year']. '</h2><ul>';
				$actualDate = $value['mois'];
			}
			
			echo '<li><a href="#' .$key. '">' .html($value['titre']). '</a></li>';
		}
		echo '</ul></div><p class="clear"></p></div>';
	}
	
	// Page d'admin
	function adminNews()
	{
		$newsAdmin = new AdminClass();
		$newsAdmin->setPage('news');
		$newsAdmin->createList(array('titre', 'date'));
		
		// Formulaire
		if(isset($_GET['add']) || isset($_GET['edit']))
		{	
			$diffText = (isset($_GET['edit'])) ? 'Modifier' : 'Ajouter';
		
			$form = new form('post', false, false);
			$form->valuesArray = $newsAdmin->formValues();
			
			$form->openFieldset($diffText. ' une formation');
				$form->addText('titre', 'Titre de la news');
				$form->addTextarea('contenu', 'Texte de la news');
				$form->addFile('thumb', 'Envoi de la miniature');
				$form->addEdit();
				$form->addHidden('date', date('Y-m-d'));
				$form->addSubmit($diffText);
			$form->closeFieldset();
			
			echo $form;
		}
	}
}
?>