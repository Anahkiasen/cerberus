<section class="detail" id="file-<?= $coveredClass ?>">
<?php
$class = $coverage[$coveredClass];

$name = $coveredClass;

$content = a::get($class, 'path');
$content = f::read($content);
$content = explode(PHP_EOL, $content);

$lines = a::get($class, 'lines');

?>
<h2><?= $name ?></h2>

<table class="table table-bordered table-condensed sortable">
	<thead>
		<th>Function</th>
		<th>CRAP</th>
		<th colspan="3">Lines</th>
	</thead>
	<tbody>
		<?php
		foreach($class['functions'] as $fname => $infos)
		{
			extract($infos);
			?>
			<tr>
				<td><?= $fname ?></td>
				<td class="crap"><?= a::get($infos, 'CRAP') ?></td>

				<td class="bar"><? progressBar($linesPerc) ?></td>
				<td class="<?= stateCoverage($linesPerc) ?> covered"><?= $linesPerc ?>%</td>
				<td class="<?= stateCoverage($linesPerc) ?> covered"><?= $linesCovered. ' / ' .$linesFunction ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

<pre class="php cover-detail">
	<code>
	<?php
	foreach($content as $n => $line)
	{
		if(!$n) continue;

		$n = $n + 1;
		$line = htmlentities($line);
		if(isset($lines[$n]))
		{
			$state = a::get($lines, $n.',count');
			$state = $state == 0 ? 'error' : 'success';
		}
		else $state = null;

		echo '<p class="line ' .$state. '"><span class="line-number">' .$n. '</span>' .$line.'</p>';
	}
	?>
	</code>
</pre>
</section>