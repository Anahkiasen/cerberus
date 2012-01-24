<? restore_error_handler() ?>
<p>Depuis cette page vous pouvez générer un sitemap ou vider le cache.
La regénération du cache peut prendre un peu de temps, les pages visitées par le crawler s'afficheront une à une ci-dessous jusqu'à l'affichage du panneau de résumé quand tout sera terminé.<br />
Ci-dessous vous pouvez appliquer des paramètres qui limiteront la portée du crawler et les pages qu'il pourra visiter.</p>
<p><em><strong>Note :</strong> Un crawler est un robot qui visite les pages une à une, comme par exemple le robot Google</em></p>

<?php
// Valeurs par défaut
$xml_name = 'cerberus/cache/sitemap_crawler.xml';
$domain = a::get($_POST, 'domain', url::domain());
$pageLimit = a::get($_POST, 'pagelimit', 0);
$trafficLimit = a::get($_POST, 'trafficlimit', 0);
$nofollow2 = a::get($_POST, 'nofollow2', "");
$extensions = a::get($_POST, 'nofollow', 'jpg,gif,png,pdf');
$extensions_crawl[] = '.(' .str_replace(',', '|', $extensions). ')';
$extensions_crawl[] = 'svn.timthumb.php';
if(!empty($nofollow2))	$extensions_crawl = array_merge($extensions_crawl, explode(',', $nofollow2));
$exploration = a::get($_POST, 'exploration', 2);
$type = a::get($_POST, 'type', 'empty');

// Formulaire de paramètres
$select = new select();
$form = new form(false, array('action' => url::rewrite('admin-crawler#results')));
$form->openFieldset('Paramètres');
	$form->addText('domain', 'Domaine à explorer', $domain);
	$form->addText('nofollow', 'Ignorer les extensions suivantes', $extensions);
	$form->addText('nofollow2', 'Ignorer les pages qui contiennent dans leur URL', $nofollow2);
	$form->addText('pagelimit', 'Limite de pages à renvoyer (0 = illimité)', $pageLimit);
	$form->addText('trafficlimit', 'Limite de traffic en MB (0 = illimité)', $trafficLimit);
		$select->newSelect('exploration', 'Portée du crawler');
		$select->appendList(array('0 - Suivre tous les liens (externes compris)', '1 - Ne suivre que les liens du même domaine' ,'2 - Ne suivre que les liens du même site', '3 - Ne suivre que les liens du même dossier'));
		$select->setValue($exploration);
		$form->insertText($select);
		
		$select->newSelect('type', 'Mode d\'exploration');
		$select->appendList(array('empty' => 'Vider le cache', 'cache' => 'Régénérer le cache', 'sitemap' => 'Créer un sitemap'), false);
		$select->setValue($type);
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
	}
	
	if($_POST['type'] != 'empty')
	{
		str::display('Le cache vient d\'être vidé, il va être régénéré page par page, veuillez patienter');
	
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
			
		$crawler->setURL($domain); 
		$crawler->addReceiveContentType("/text\/html/"); 
		$crawler->addNonFollowMatch("/" .implode('|', $extensions_crawl). "/i"); 
		
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
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<urlset 
			xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
			xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 
			http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
		
		echo '<h2>Sitemap généré</h2><ul style="list-style-type:square">';
		foreach($report['crawled'] as $page) 
		{
			echo '<li>' .$page. '</li>';
			$xml .= "\n<url>\n\t<loc>" .$page. "</loc>\n\t<changefreq>monthly</changefreq>\n</url>";
		}
		echo '</ul></div>';
		$xml .= '</urlset>';
		
		f::write($xml_name, $xml);
		str::display(str::link($xml_name, 'Télécharger le sitemap généré'));
	}
	else str::display('Le cache vient d\'être vidé');
}
?> 