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
	{
		$tests[$k] = str::parse($v, 'json');
	}
	return $tests;
}

// Rearranging the tests to readable stuff
function readTests($tests)
{
	$results = array();

	// Getting title
	$results['title'] = a::get($tests, '0,suite');

	foreach($tests as $test)
	{
		if(a::get($test, 'event') == 'test')
		{
			$function = a::get($test, 'test');
			$status = a::get($test, 'status') == 'pass';
			$message = a::get($test, 'message');
			$results[$function] = array(
				'status' => $status,
				'message' => $message);
		}
	}

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
a::show($json);
?>
</head>

<body>
	<div class="container">
	<?php

	foreach($tests as $tests)
	{
		$title = a::get($tests, 'title');
		echo '<h1>' .$title. '</h1>';
		$tests = a::remove($tests, 'title');

		foreach($tests as $name => $test)
		{
			echo '<h2>' .$name. '</h2>';
			echo a::get($test, 'status')
				? str::display('Success', 'success')
				: str::display('Error', 'error');
		}
	}
	?>
	</div>
</body>
</html>
<?php require('../close.php');