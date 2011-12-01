<? restore_error_handler() ?>
<p>Depuis cette page vous pouvez générer un sitemap ou vider le cache. La regénération du cache peut prendre un peu de temps, les pages visitées par le spider s'afficheront une à une ci-dessous jusqu'à l'affichage du panneau de résumé quand tout sera terminé.</p>

<?= 
str::slink(NULL, '<p id="left" class="infoblock" style="text-align:center; background-color:#8A976A">Régénérer le cache</p>', array('crawl' => true)).
str::slink(NULL, '<p id="left" class="infoblock" style="text-align:center; background-color:#8A976A">Créer un sitemap</p>', array('crawl' => 'sitemap'))
?>
<p class="clear"></p>

<?php 

if(get('crawl'))
{
	if(get('crawl') == 'sitemap') content::start();
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
	$domain = get('domain', url::domain());
	$trafficLimit = get('traffic', 0); // 2MB
	
	$crawler->setURL($domain); 
	$crawler->addReceiveContentType("/text\/html/"); 
	$crawler->addNonFollowMatch("/.(jpg|gif|png)|class.timthumb.php/i"); 
	
	$crawler->setCookieHandling(true); 
	$crawler->setTrafficLimit($trafficLimit * 1000);
	
	$crawler->go(); 
	$report = $crawler->getReport(); 
	
	// Résumé du crawler
	if(get('crawl') == 'sitemap')
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
	
	echo '<h2>Sitemap généré</h2><ul style="list-style-type:square">';
	foreach($report['crawled'] as $page) echo '<li>' .$page. '</li>';
	echo '</ul></div>';
}
?> 