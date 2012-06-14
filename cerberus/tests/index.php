<?php
$init = '../../';
require('../init.php');

// Setting title
head::title('Unit Testing Summary');

// Main styles
dispatch::assets('!styles', 'jquery', 'unit-testing');
dispatch::googleFonts('Open Sans:100,400,700', 'Raleway:100');

// Reading available JSON tests
$json   = parseTests('phpunit.json');
$suites = readTests($json);

// Parsing the misconstructed JSON reports (fuckers)
function parseTests($test)
{
	$tests = f::read($test);
	$tests = preg_replace('#{([^}]+)}#', '{$1}\n', $tests);
	$tests = explode('\n', $tests);

	foreach($tests as $k => $v)
		$tests[$k] = str::parse($v, 'json');

	return $tests;
}

// Rearranging the tests to readable stuff
function readTests($tests)
{
	$results = array();
	$errors  = 0;
	$folder = dir::last(getcwd());


	foreach($tests as $test)
	{
		$suite = a::get($test, 'suite');
		$suite = explode('::', $suite);
		$suite = a::get($suite, 0);
		$event = a::get($test, 'event');

		if($event == 'suiteStart' and $suite !== $folder)
		{
			if(isset($className))
			{
				$results[$className]['errors'] = $errors;
				$errors = 0;
			}
			$className = $suite;
		}
		if($event == 'test')
		{
			$function = a::get($test, 'test');
			$status   = a::get($test, 'status') == 'pass';
			$message  = a::get($test, 'message');

			if(!$status) $errors++;
			$results[$className][$function] = array(
				'status' => $status,
				'message' => $message);
		}
	}

	$results['errors'] = $errors;
	return $results;
}
?>
</head>

<body>
	<section id="corps">
		<h1>Unit Testing Results</h1>

		<h2>Table of contents</h2>
		<ul id ="toc">
			<?php
			foreach($suites as $title => $osef)
				echo '<li>' .str::link('#'.str::slugify($title), str::remove('Test', $title)). '</li>';
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

		<?php
		foreach($suites as $title => $tests)
		{
			if(sizeof($tests) == 1) continue;
			$testCount = sizeof($tests) - 1;
			$passed = $testCount - intval(a::get($tests, 'errors'));

			$strippedTitle = str::remove('Test', $title);
			echo '<section id="' .str::slugify($title). '">';
				echo '<h2>' .$strippedTitle. ' (' .$passed. '/' .$testCount. ')</h2>'.PHP_EOL;

				foreach($tests as $name => $infos)
				{
					if($name == 'errors' or $name == 'title') continue;
					$name     = str::remove($title.'::test', $name);
					$dataSet  = preg_replace('/(.+) with data set #([0-9]+) \(.+\)/is', '$2', $name);
					$provider = preg_replace('/(.+) with data set #([0-9]+) \((.+)\)/is', '$3', $name);
					$name     = preg_replace('/with data set #([0-9]+) \((.+)\)/', null, $name);
					$message  = a::get($infos, 'message');

					if(!$message) $message = a::get($infos, 'status') ? 'Success' : 'Error';

					echo '<article>'.PHP_EOL;
					echo '<h3>' .$name. '</h3>'.PHP_EOL;
					if($provider != $name) echo str::display('#' .$dataSet.' -> ('.$provider.')', 'info');
					echo a::get($infos, 'status')
						? str::display($message, 'success')
						: str::display($message, 'error');
					echo '</article>'.PHP_EOL;
				}
			echo '</section>';
		}
		?>
	</section>
</body>
</html>
<?php require('../close.php');