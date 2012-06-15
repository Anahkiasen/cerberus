<?php
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
			if(isset($className) and isset($results[$className]))
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

function readCoverage()
{
	$coverageXML = f::read('coverage.xml', 'xml');
	//a::show($coverageXML);

	// Reading the code coverage
	$timestamp = a::get($coverageXML, '@attributes,timestamp');
	$timestamp = date('Y-m-d H:i:s', $timestamp);

	$coverageXML = a::get($coverageXML, 'project,file');
	foreach($coverageXML as $file)
	{
		// Filename
		$filepath = a::get($file, '@attributes,name');
		$filename = f::filename($filepath);

		// Class name
		$className = a::get($file, 'class,@attributes,name');
		if(!$className) continue;
		$classCrap = 0;
		$metrics = a::get($file, 'class,metrics,@attributes');

		// Type of the class
		$type = str::find('/cerberus/class/', $filepath)
			? 'class'
			: 'tools';

		// Coverage informations
		if(!$metrics)
		{
			$metrics = array(
				'statements' => 1,
				'coveredstatements' => 0);
		}
		if($metrics)
		{
			extract($metrics);
			$coverage[$className]['metrics'] = array(
				'methods'           => $methods,
				'methodsCovered'    => $coveredmethods,
				'methodsPerc'       => round($coveredmethods * 100 / $methods, 2),
				'statements'        => $statements,
				'statementsCovered' => $coveredstatements,
				'statementsPerc'    => round($coveredstatements * 100 / $statements, 2),
				'elements'          => $elements,
				'elementsCovered'   => $coveredelements,
				'elementsPerc'      => round($coveredelements * 100 / $elements, 2),
				);
		}

		// Current function
		$functionCurrent = null;
		$functionLines   = 0;
		$functionCovered = 0;

		// Lines
		foreach($file['line'] as $line)
		{
			$line = a::get($line, '@attributes');
			$type = a::get($line, 'type');

			$num = a::get($line, 'num');
			$coverage[$className]['lines'][$num] = $line;

			// CC
			$count = a::get($line, 'count');
			$functionLines++;
			if($count != 0) $functionCovered++;

			// Method
			if($type != 'stmt')
			{
				$crap = a::get($line, 'crap');
				if($crap) $classCrap += round($crap, 2);

				// Function
				$function = a::get($line, 'name');
				if($functionCurrent and $function and $function != $functionCurrent)
				{
					$coverage[$className]['functions'][$function] = array(
						'linesFunction' => $functionLines,
						'linesCovered'  => $functionCovered,
						'linesPerc'     => round($functionCovered * 100 / $functionLines, 2),
						'CRAP'          => $crap
						);

					$functionCurrent = $function;
					$functionLines   = 0;
					$functionCovered = 0;
				}
				if(!$functionCurrent) $functionCurrent = $function;
			}
		}

		// Saving informations
		$coverage[$className]['CRAP'] = $classCrap;
		$coverage[$className]['file'] = $filename;
		$coverage[$className]['path'] = $filepath;
	}

	ksort($coverage);
	return $coverage;
}

function stateCoverage($percentage)
{
	$median = 100 / 4;
	if($percentage >= (100 - $median)) return 'success';
	elseif($percentage <= $median) return 'danger';
	elseif($percentage > $median and $percentage < ($median * 2)) return 'warning';
	else return 'info';
}

function progressBar($percentage)
{
	?>
	<div class="progress progress-<?= stateCoverage($percentage) ?> progress-striped">
		<div class="bar" style="width: <?= $percentage ?>%"></div>
	</div>
	<?
}
