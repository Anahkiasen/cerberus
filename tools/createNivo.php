<?php
function createNivo($path, $largeur, $hauteur, $options = '', $shuffle = false)
{
	echo '<div id="' .$path. '" style="height:' .$hauteur. 'px; max-width: ' .$largeur. 'px; margin:auto">';
	$arrayImages = glob('file/' .$path. '/*.jpg');
	if($shuffle == TRUE) shuffle($arrayImages);
	foreach($arrayImages as $file)
		echo '<img src="file/timthumb.php?src=' .$file. '&w=' .$largeur. '&h=' .$hauteur. '&zc=1" />';
	echo '</div>';
		
	?>
	<script type="text/javascript">
		$("#<?= $path ?>").nivoSlider(<?= $options ?>);
	</script>
	<?	
}
?>