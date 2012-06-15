<?php
define('SQL', false);
$init = '../../';
require('../init.php');
require('lib/unitParser.php');

// Setting title
head::title('Unit Testing Summary');

// Main styles
dispatch::setGuess(false);
dispatch::assets('bootstrap', 'jquery', 'unit-testing');
dispatch::googleFonts('Open Sans:100,400,700', 'Raleway:100');

// Tablesorter
dispatch::addJS('lib/jquery.tablesorter.min.js');
dispatch::plugin('tablesorter', '.sortable');

// Highlight.js
dispatch::addCSS('lib/solarized_light.css');
dispatch::addJS('lib/highlight.pack.js');
dispatch::addJS();
?><script>
	$(document).ready(function() {
	  $('.cover-detail').each(function(e) {hljs.highlightBlock(e)});
	});
</script><?
dispatch::closeJS();

// Reading available JSON tests
$json     = parseTests('phpunit.json');
$suites   = readTests($json);
$coverage = readCoverage();
?>
</head>

<body>
	<nav id="toc">
		<h3>Code coverage</h3>
		<ul class="nav nav-pills">
		<?php
		foreach($coverage as $title => $osef)
			if($title != 'errors')
			{
				$link = url::reload(array('coverage' => $title)).'#file-'.$title;
				$title = str::remove('Test', $title);
				echo '<li>' .str::link($link, ucfirst($title)). '</li>';
			}
		?>
		</ul>
		<div class="alert-block alert">
			<h4 class="alert-header">Global coverage</h4><br />
			<? progressBar(60) ?>
		</div>

		<h3>Unit testing results</h3>
		<ul class="nav nav-pills">
		<?php
		foreach($suites as $title => $osef)
			if($title != 'errors')
			{
				$link = url::reload('results').'#'.str::slugify($title);
				$title = str::remove('Test', $title);
				echo '<li>' .str::link($link, $title). '</li>';
			}
		?>
		</ul>
		<?php
		$pass = a::extract($suites, 'errors');
		$pass = array_sum($pass);
		$color = $pass == 0 ? 'success' : 'error';
		str::display(
			str::img('https://secure.travis-ci.org/Anahkiasen/cerberus.png').
			' Number of errors found : ' .$pass, $color);
		$suites = a::remove($suites, 'errors');
		?>
	</nav>




	<section id="coverage">
		<h1>Tests coverage</h1>

		<table class="sortable table table-condensed table-bordered">
			<thead>
				<th>Class</th>
				<th>File</th>
				<th>CRAP</th>
				<th colspan="3">Lines</th>
				<th colspan="3">Functions</th>
				<th colspan="3">Classes</th>
			</thead>
			<tbody>
		<?php
		foreach($coverage as $class => $infos)
		{
			$metrics = a::get($infos, 'metrics');
			if($metrics) extract($metrics);
			$link = url::reload(array('coverage' => $class)).'#file-'.$class;
			?>
			<tr>
				<td class="class"><?= str::link($link, ucfirst($class)) ?></td>
				<td class="file"><?= $infos['file'] ?></td>

				<td class="crap"><?= a::get($infos, 'CRAP') ?></td>

				<td class="bar"><? progressBar($statementsPerc) ?></td>
				<td class="<?= stateCoverage($statementsPerc) ?> covered"><?= $statementsPerc ?></td>
				<td class="<?= stateCoverage($statementsPerc) ?> covered"><?= $statementsCovered. ' / ' .$statements ?></td>

				<td class="bar"><? progressBar($methodsPerc) ?></td>
				<td class="<?= stateCoverage($methodsPerc) ?> covered"><?= $methodsPerc ?></td>
				<td class="<?= stateCoverage($methodsPerc) ?> covered"><?= $methodsCovered. ' / ' .$methods ?></td>

				<td class="bar"><? progressBar($elementsPerc) ?></td>
				<td class="<?= stateCoverage($elementsPerc) ?> covered"><?= $elementsPerc ?></td>
				<td class="<?= stateCoverage($elementsPerc) ?> covered"><?= $elementsCovered. ' / ' .$elements ?></td>
			</tr>
			<?
		}
		?>
			</tbody>
		</table>
	</section>

	<section id="corps">
		<?php if(isset($_GET['results'])) include('lib/testResults.php') ?>
	</section>

	<?php
	$coveredClass = r::get('coverage');
	if($coveredClass and isset($coverage[$coveredClass])) include('lib/classCoverage.php');
	?>
</body>
</html>
<?php require('../close.php');
