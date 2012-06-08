<?php
class upload
{
	/**
	 * Scan for an uploaded file and treat it
	 *
	 * @param  string $field        The $_FILES field to check
	 * @param  string $destination  The future path of the file and if wanted, a new name/extension. To use
	 *                              the old ones use the flags {name} and {extension}
	 * @param  array  $params       An array of parameters to configure the upload
	 *                                -- allowed : an array of allowed MIME types
	 *                                -- maxsize : The maximum size of the file
	 *                                -- overwrite : Allow overwrite of any found files
	 *                                -- sanitize : Sanitize the name of the file or not
	 * @return array               An array with informations about how the upload went
	 */
	public static function file($field, $destination, $params = array())
	{
		// Getting main parameters
		$allowed   = a::get($params, 'allowed',   config::get('upload.allowed'));
		$maxsize   = a::get($params, 'maxsize',   config::get('upload.maxsize', self::max_size()));
		$overwrite = a::get($params, 'overwrite', config::get('upload.overwrite'));
		$sanitize  = a::get($params, 'sanitize',  true);
		$file      = a::get($_FILES, $field);

		// Forcing the allowed filetypes as array
		if(!is_array($allowed)) $allowed = array($allowed);

		// If the uploaded file was not found
		if(empty($file))
			return array('status' => 'error', 'msg' => l::get('upload.errors.missing-file'));

		// Getting informations about the file
		$name      = a::get($file, 'name');
		$type      = a::get($file, 'type');
		$tmp_name  = a::get($file, 'tmp_name');
		$error     = a::get($file, 'error');
		$size      = a::get($file, 'size');
		$msg       = false;
		$extension = self::mime_to_extension($type, f::extension($name));

		// Normalize the name
		$fname = ($sanitize) ? str::slugify(f::name($name)) : f::name($name);

		// If no name/extension flags are set, add them
		if(!str::find('{extension}', $destination))
		{
			if(substr($destination, -1) != '/') $destination .= '/';
			$destination .= '{name}.{extension}';
		}

		// Replace name and/or extension with the old ones
		$destination = str_replace('{name}', $fname, $destination);
		$destination = str_replace('{extension}', $extension, $destination);

		// Main errors
		if(file_exists($destination) && $overwrite == false)
			return array('status' => 'error', 'msg' => l::get('upload.errors.file-exists'), );

		if(empty($tmp_name))
			return array('status' => 'error', 'msg' => l::get('upload.errors.missing-file'), );

		if($error != 0)
			return array('status' => 'error', 'msg' => l::get('upload.errors.invalid-upload'), );

		if($size > $maxsize)
			return array('status' => 'error', 'msg' => l::get('upload.errors.too-big'), );

		if(!in_array($type, $allowed))
			return array('status' => 'error', 'msg' => l::get('upload.errors.invalid-file').': '.$type, );

		// Try to change the permissions for the destination
		@chmod(dirname($destination), 0777);

		if(!@copy($tmp_name, $destination))
			return array('status' => 'error', 'msg' => l::get('upload.errors.move-error'), );

		// Try to change the permissions for the final file
		@chmod($destination, 0777);

		// Return results array
		return array(
			'status'    => 'success',
			'msg'       => l::get('upload.success'),
			'type'      => $type,
			'extension' => $extension,
			'file'      => $destination,
			'size'      => $size,
			'name'      => f::filename($destination));
	}

	/**
	 * Checks a Kirby array for status
	 *
	 * @param  array   $array  An array to scan
	 * @return string
	 */
	public static function status($array)
	{
		str::display($array['msg'], $array['status']);
	}

	public static function max_size()
	{
		$val = ini_get('post_max_size');
		$val = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		switch($last)
		{
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	public static function mime_to_extension($mime, $default = '')
	{
		$types = array(
			'image/jpeg'               => 'jpg',
			'image/pjpeg'              => 'jpg',
			'image/png'                => 'png',
			'image/x-png'              => 'png',
			'image/gif'                => 'gif',
			'text/plain'               => 'txt',
			'text/html'                => 'html',
			'application/xhtml+xml'    => 'html',
			'text/javascript'          => 'js',
			'text/css'                 => 'css',
			'text/rtf'                 => 'rtf',
			'application/msword'       => 'doc',
			'application/msexcel'      => 'xls',
			'application/vnd.ms-excel' => 'xls',
			'application/mspowerpoint' => 'ppt',
			'application/pdf'          => 'pdf',
			'application/zip'          => 'zip');
		return a::get($types, $mime, $default);
	}
}
