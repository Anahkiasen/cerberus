<?php
/*
	Fonction sunlink
	# Supprime un fichier ou un dossier, et le vide facultativement
	
	$file
		Fichier/dossier à supprimer
	$empty
		TRUE	Vide le dossier au lieu de le supprimer
		FALSE	Supprime le dossier
*/
function sunlink($file, $empty = false)
{
	if(file_exists($file))
	{
		if(!is_dir($file)) return unlink($file);
		else
		{
			if(substr($file, -1) == "/") $file = substr($file, 0, -1);
		
			if(!file_exists($file) || !is_dir($file)) return false;
			elseif(!is_readable($file)) return false;
			else
			{
				// Lecture du dossier
				$fileHandle = opendir($file);
				while ($contents = readdir($fileHandle))
				{
					if($contents != '.' && $contents != '..')
					{
						$path = $file . "/" . $contents;
						
						// Suppression du fichier/dossier
						if(is_dir($path)) sunlink($path);
						else unlink($path);
					}
				}
				closedir($fileHandle);
		
				if($empty == false and !rmdir($file)) return false;
				return true;
			}
		}
	}
	else return false;
}
?>