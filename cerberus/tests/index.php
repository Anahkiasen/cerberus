<?php
$init = '../../';
require('../init.php');

// Setting title
head::title('Unit Testing Summary');

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

	// Getting title
	$results['title'] = a::get($tests, '0,suite');

	foreach($tests as $test)
	{
		if(a::get($test, 'event') == 'test')
		{
			$function = a::get($test, 'test');
			$status   = a::get($test, 'status') == 'pass';
			$message  = a::get($test, 'message');

			if(!$status) $errors++;
			$results[$function] = array(
				'status' => $status,
				'message' => $message);
		}
	}

	$results['errors'] = $errors;
	return $results;
}

// Reading available JSON tests
$files = glob('*.json');
foreach($files as $file)
{
	$name = f::filename($file);
	$parsed = parseTests($name);

	$json[$name] = $parsed;
	$tests[$name] = readTests($parsed);
}

// a::show($json);
?>
</head>

<body>
	<div class="container">
		<h1>Summary</h1>
		<?php
		$pass = a::extract($tests, 'errors');
		$pass = array_sum($pass);
		$color = $pass == 0 ? 'success' : 'error';
		str::display('Number of errors found : ' .$pass, $color);
		?>

		<?php
		foreach($tests as $test)
		{
			$title = a::get($test, 'title');
			$title = str::remove('Test', $title);
			echo '<h2 style="clear:both">' .$title. '</h2><div class="row">';

			foreach($test as $name => $infos)
			{
				if($name == 'errors' or $name == 'title') continue;
				$name = str::remove(a::get($test, 'title').'::test', $name);

				echo '<div class="span3"><h3>' .$name. '</h3>';
				echo a::get($infos, 'status')
					? str::display('Success', 'success')
					: str::display('Error', 'error');
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div>
</body>
</html>
<?php require('../close.php');