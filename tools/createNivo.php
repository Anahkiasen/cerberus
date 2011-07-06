<?php
function createNivo($path, $largeur, $hauteur, $options = '')
{
	echo '<div id="' .$path. '" style="height:' .$hauteur. 'px; width: ' .$largeur. 'px; margin:auto">';
	foreach(glob('file/' .$path. '/*.jpg') as $file)
		echo '<img src="file/timthumb.php?src=' .$file. '&w=' .$largeur. '&h=' .$hauteur. '&zc=1" />';
	echo '</div>';
		
	?>
	<script type="text/javascript">
		$("#<?= $path ?>").nivoSlider(<?= $options ?>);
	</script>
	<?	
}
?>