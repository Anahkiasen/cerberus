<?php
function getDirectorySize($path, $sizeFormat = true)
{
	$totalsize = 0;
	$totalcount = 0;
	$dircount = 0;
	if ($handle = opendir ($path))
	{
		while (false !== ($file = readdir($handle)))
		{
			$nextpath = $path . '/' . $file;
			if ($file != '.' && $file != '..' && !is_link ($nextpath))
			{
				if (is_dir ($nextpath))
				{
					$dircount++;
					$result = getDirectorySize($nextpath);
					$totalsize += $result['size'];
					$totalcount += $result['count'];
					$dircount += $result['dircount'];
				}
				elseif (is_file ($nextpath))
				{
					$totalsize += filesize ($nextpath);
					$totalcount++;
				}
			}
		}
	}
	closedir ($handle);
	
	if($sizeFormat == true)
	{
		if($totalsize < 1024) return $totalsize. ' bytes';
		elseif($totalsize < (1024 * 1024))
		{
			$totalsize = round($totalsize / 1024, 1);
			return $totalsize. ' KB';
		}
		elseif($totalsize < (1024 * 1024 * 1024))
		{
			$totalsize = round($totalsize / (1024 * 1024), 1);
			return $totalsize. ' MB';
		}
		else
		{
			$totalsize = round($totalsize / (1024 * 1024 * 1024), 1);
			return $totalsize. ' GB';
		}
	}
	
	$total['size'] = $totalsize;
	$total['count'] = $totalcount;
	$total['dircount'] = $dircount;
	return $total;
}
?>