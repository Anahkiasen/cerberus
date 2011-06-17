<?php
function printr($array)
{
	echo '<div class="li" style="text-align:justify">';
	foreach($array as $key => $value)
	{
		if(isset($value) && !is_array($value)) echo $key. ' — <span class="b">' .nl2br(htmlentities($value)). '</span><br />';
		elseif(is_array($value) && !in_array($key, array('en', 'fr', 'es')))
		{
			echo '<p class="print" style="border: none">—— ' .$key. ' ——</p><p class="print">';
			foreach($value as $v1 => $v2)
			{
				if(isset($v2) && !is_array($v2)) echo $v1. ' — '.nl2br(htmlentities($v2)). '<br />'; 
				elseif(is_array($v2)) { echo '<p class="print2" style="border: none">—— ' .$v1. ' ——</p><p class="print2">'; foreach($v2 as $v3 => $v4)  echo $v3. ' — '.nl2br(htmlentities($v4)). '<br />'; echo '</p>'; }
			}
			echo '</p>';
		}
		else echo '<span style="color: grey; font-weight: normal">Clé ' .$key. ' à valeur indéfinie</span><br />';
	}
	echo '</div>';
}
?>