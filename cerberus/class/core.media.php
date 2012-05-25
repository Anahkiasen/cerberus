<?php
class media extends dispatch
{
	/**
	 * Adds a picture using the current PATH to the images folder
	 * 
	 * @param  string  $image       The name of the picture
	 * @param  string  $alt         Alt attribute for the picture
	 * @param  array   $attributes  Other attributes
	 * @return string               Formatted image tag
	 */
	static function image($name, $alt = NULL, $attributes = NULL)
	{
		echo str::img(self::path(PATH_COMMON.'{images}/' .$name), $alt, $attributes);
	}
	
	/**
	 * Display an image using TimThumb to resize/recrop
	 * 
	 * @param  string  $file The name and path of the picture
	 * @param  int     $width The desired width
	 * @param  int     $height The desired $height
	 * @param  array   $params Supplementary options to pass to TimThumb 
	 */
	static function timthumb($file, $width = NULL, $height = NULL, $params = array())
	{
		if(!empty($width)) $params['w'] = $width;
		if(!empty($height)) $params['h'] = $height;
		
		if(!str::find('http', $file))
		{
			$file = PATH_FILE.str::remove(PATH_FILE, $file);
			$file = (str::find('../', $file))
				? realpath($file)
				: $file;
		}
			
		return PATH_CORE.'class/plugins/timthumb.php?src=' .$file. '&' .a::glue($params, '&', '=');
	}
	
	/**
	 * Creates a slideshow (currently based on nivoSlider)
	 * 
	 * @param string  $folder  The folder containing the images to use
	 * @param int     $width   The width of the slideshow
	 * @param int     $height  The height of the slideshow
	 * @param mixed   $params  The parameters to pass, as a JSON or PHP array
	 * @param boolean $shuffle Whether PHP should shuffle the images found in the directory or not
	 */
	static function slideshow($folder, $width, $height, $params = array(), $shuffle = true)
	{
		// Create a HTML-safe id for the block
		$id_block = str::slugify($folder);
		
		// Fetch the pictures
		$images = glob(PATH_FILE.$folder.'/*.jpg');
		
		// Shuffle them if asked
		if($shuffle) shuffle($images);
		
		// Creating the block
		echo "<div id='" .$id_block. "' style='height: " .$height. "px; max-width: " .$width. "px; margin: auto'>".PHP_EOL;
		foreach($images as $i) echo "\t".
			str::img(
				self::timthumb(
					$folder.'/'.basename($i),
					$width,
					$height)).PHP_EOL;
		echo '</div>'.PHP_EOL;
		
		// Add the Javascript code to the page
		dispatch::plugin('nivoSlider', '#'.$id_block, $params);
	}

	/**
	 * Add a Flash animation to the code with the corresponding SWFObject code
	 * 
	 * @param string  $swf         The name of the .swf file
	 * @param string  $bloc        The name of the div to put the animation in
	 * @param int	   $width       The width of the animation
	 * @param int     $height      The height of the animation
	 * @param array   $flashvars   Some variables to pass to Flash
	 * @param array   $params      The Flash animation parameters
	 * @param array   $attributes  Attributes of the object element
	 */
	static function swf($swf, $bloc, $width, $height, $flashvars = NULL, $params = NULL, $attributes = NULL)
	{
		$flashvars  = ($flashvars)  ? json_encode($flashvars)  : '{}';
		$params     = ($params)     ? json_encode($params)     : '{}';
		$attributes = ($attributes) ? json_encode($attributes) : '{}';
		
		$swfobject = 'swfobject.embedSWF("' .PATH_COMMON. 'swf/' .$swf. '.swf", "' .$bloc. '", "' .$width. '", "' .$height. '", "9.0.0", false, ' .$flashvars. ', ' .$params. ', ' .$attributes. ');';
		dispatch::addJS($swfobject);
	}
}
?>
