<h1>Tests Results</h1>

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
	ksort($tests);
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