<?php
$init = '../../';
require('../init.php');

// Setting title
head::title('Unit Testing Summary');

// Reading available JSON tests
$json = parseTests('phpunit.json');
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
	<div class="container">
		<h1>Unit Testing Results</h1>
		<?php
		$pass = a::extract($suites, 'errors');
		$pass = array_sum($pass);
		$color = $pass == 0 ? 'success' : 'error';
		str::display('Number of errors found : ' .$pass, $color);
		$suites = a::remove($suites, 'errors');
		?>

		<?php
		foreach($suites as $title => $tests)
		{
			$strippedTitle = str::remove('Test', $title);
			echo '<h2 style="clear:both">' .$strippedTitle. '</h2><div class="row">';

			foreach($tests as $name => $infos)
			{
				if($name == 'errors' or $name == 'title') continue;
				$name = str::remove($title.'::test', $name);
				$message = a::get($infos, 'message');
				if(!$message) $message = a::get($infos, 'status') ? 'Success' : 'Error';

				echo '<div class="span3"><h3>' .$name. '</h3>';
				echo a::get($infos, 'status')
					? str::display($message, 'success')
					: str::display($message, 'error');
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div>
</body>
</html>
<?php require('../close.php');