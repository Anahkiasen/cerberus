<? restore_error_handler() ?>
<p>Depuis cette page vous pouvez générer un sitemap ou vider le cache.
La regénération du cache peut prendre un peu de temps, les pages visitées par le crawler s'afficheront une à une ci-dessous jusqu'à l'affichage du panneau de résumé quand tout sera terminé.<br />
Ci-dessous vous pouvez appliquer des paramètres qui limiteront la portée du crawler et les pages qu'il pourra visiter.</p>
<p><em><strong>Note :</strong> Un crawler est un robot qui visite les pages une à une, comme par exemple le robot Google</em></p>

<?php
$select = new select();
$form = new form(false, array('action' => rewrite('#results')));
$form->openFieldset('Paramètres');
	$form->addText('domain', 'Domaine à explorer', url::domain());
	$form->addText('nofollow', 'Ignorer les extensions suivantes', 'jpg,gif,png');
	$form->addText('pagelimit', 'Limite de pages à renvoyer (0 = illimité)', "0");
	$form->addText('trafficlimit', 'Limite de traffic (0 = illimité)', "0");
		$select->newSelect('exploration', 'Portée du crawler');
		$select->appendList(array('0 - Suivre tous les liens (externes compris)', '1 - Ne suivre que les liens du même domaine' ,'2 - Ne suivre que les liens du même site', '3 - Ne suivre que les liens du même dossier'));
		$select->setValue(2);
		$form->insertText($select);
		
		$select->newSelect('type', 'Mode d\'exploration');
		$select->appendList(array('cache' => 'Régénérer le cache', 'sitemap' => 'Créer un sitemap'), false);
		$form->insertText($select);
	$form->addSubmit('Lancer le crawler');
$form->closeFieldset();
echo $form. '<p id="results"></p>';

if(isset($_POST['nofollow']))
{
	if($_POST['type'] == 'sitemap') content::start();
	else
	{
		content::uncache();
		f::remove_folder('assets/file/cache');
		prompt('Le cache vient d\'être vidé, il va être régénéré page par page, veuillez patienter');
	}
	
	set_time_limit(10000); 
	include("cerberus/class/crawler/crawler.crawler.php"); 
	
	// Génération du cache
	class MyCrawler extends PHPCrawler	
	{ 
		function handlePageData(&$page_data)	
		{ 
			// Affichage des informations de la page reçue
			$type = (str::find('404', $page_data['header'])) ? 'red' : 'white';
			echo '<div class="cerberus_debug" style="text-align:left">
			<h3 style="margin:0">' .url::strip_query($page_data['url']). '</h3>
			<span style="color: ' .$type. '"><strong>Type :</strong> ' .strtok($page_data["header"], "\n"). '</span><br />';
			if(!empty($page_data["referer_url"])) echo '<strong>Page d\'origine :</strong> ' .url::strip_query($page_data["referer_url"]). '<br />';
			
			echo '<strong>Contenu :</strong> ';
			echo ($page_data['received'])
				? round($page_data['bytes_received'] / 1000, 2). ' kb reçus'
				: '0 bytes reçus';
				echo '</div>';
			 
			flush(); 
		} 
	} 
	
	$crawler = new MyCrawler(); 
	$domain = a::get($_POST, 'domain', 0);
	$pageLimit = a::get($_POST, 'pagelimit', 0);
	$trafficLimit = a::get($_POST, 'trafficlimit', 0);
	$extensions = str_replace(',', '|', $_POST['nofollow']);
	if(empty($extensions)) $extensions = 'jpg|gif|png';
	
	$crawler->setURL($domain); 
	$crawler->addReceiveContentType("/text\/html/"); 
	$crawler->addNonFollowMatch("/.(" .$extensions. ")|class.timthumb.php/i"); 
	
	$crawler->setCookieHandling(true); 
	$crawler->setTrafficLimit($trafficLimit * 1000);
	$crawler->setPageLimit($pageLimit);
	
	$crawler->go(); 
	$report = $crawler->getReport(); 
	
	// Résumé du crawler
	if($_POST['type'] == 'sitemap')
	{
		$summary = content::end(true);
		echo '<div class="cerberus_debug" style="width: 100%">';
	}
	else
	{
		echo '
			<div class="cerberus_debug" style="width: 100%"><h2 style="margin-top: 0">Résumé</h2>
			<strong>Liens suivis :</strong> ' .$report['links_followed']. '<br />
			<strong>Fichiers générés :</strong> ' .$report['files_received']. '<br />
			<strong>Taille du contenu reçu :</strong> ' .round($report['bytes_received'] / 1000, 2). ' kb<br />
			<strong>Temps d\'exécution :</strong> ' .round($report['process_runtime']). ' secondes';
		if($report['traffic_limit_reached']) echo '<br />Limite de traffic atteinte';
	}
	
	// Sitemap
	echo '<h2>Sitemap généré</h2><ul style="list-style-type:square">';
	foreach($report['crawled'] as $page) echo '<li>' .$page. '</li>';
	echo '</ul></div>';
}
?> 