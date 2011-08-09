<?php
function sunlink($file, $empty = false)
{
	if(file_exists($file))
	{
		if(!is_dir($file)) unlink($file);
		else
		{
			if(substr($directory, -1) == "/") $directory = substr($directory, 0, -1);
		
			if(!file_exists($directory) || !is_dir($directory)) return false;
			elseif(!is_readable($directory)) return false;
			else
			{
				// Lecture du dossier
				$directoryHandle = opendir($directory);
				while ($contents = readdir($directoryHandle))
				{
					if($contents != '.' && $contents != '..')
					{
						$path = $directory . "/" . $contents;
					   
						// Suppression du fichier/dossier
						if(is_dir($path)) deleteFolder($path);
						else unlink($path);
					}
				}
				closedir($directoryHandle);
		
				if($empty == false and !rmdir($directory)) return false;	   
				return true;
			}
		}
	}
}
?>