<?php
/**
 *
 * Validator
 *
 * Makes input validation easier
 *
 * @package Kirby, Cerberus
 */
class valid
{
	/**
	 * Validate a string for a certain type
	 *
	 * @param  string $string A string
	 * @param  string $type   A string type
	 * @return boolean        Whether the string is valid or not
	 */
	public static function check($string = null, $type = null)
	{

		switch($type)
		{
			case 'facultative':
				return true;
				break;

			case 'url':
				return !empty($string) and self::url($string);
				break;

			case 'email':
				return !empty($string) and self::email($string);
				break;

			case 'nom':
			case 'prenom':
			case 'name':
				return !empty($string) and !preg_match('/[\d]+/', $string);
				break;

			case 'nombre':
			case 'number':
				return !empty($string) and preg_match('/^[\d \.,]+$/', $string);
				break;

			default:
				return !empty($string);
				break;
		}
	}

	/**
     * Core method to create a new validator
     *
     * @param  string  $string  A string
     * @param  array   $options Options to format (min_length, max_length, format[regex])
     * @return boolean
     */
  	public static function string($string, $options)
	{
		$format     = null;
		$min_length =
		$max_length = 0;

		if(is_array($options))
			extract($options);

		if($format && !preg_match('/^[' .$format. ']*$/is', $string)) return false;
		if($min_length && str::length($string) < $min_length) return false;
		if($max_length && str::length($string) > $max_length) return false;

		return true;
	}

	//////////////////////////////////////////////////////////////////
	/////////////////////// CORE FUNCTIONS ///////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
     * Checks for a valid password
     *
     * @param  string  $password
     * @return boolean
     */
	public static function password($password)
	{
		return self::string($password, array('min_length' => 4));
	}

	/**
     * Checks for two valid, matching password
     *
     * @param  string  $password1
     * @param  string  $password2
     * @return boolean
     */
	public static function passwords($password1, $password2)
	{
		return (
			$password1 == $password2 and
		 	self::password($password1) and
		 	self::password($password2));
	}

	/**
     * Checks for valid date
     *
     * @param  string  $date
     * @return boolean
     */
	public static function date($date)
	{
		$time = strtotime($date);
		if(!$time) return false;

		$year  = date('Y', $time);
		$month = date('m', $time);
		$day   = date('d', $time);

		return (checkdate($month, $day, $year)) ? $time : false;
	}

	/**
     * Checks for valid email address
     *
     * @param  string  $email
     * @return boolean
     */
	public static function email($email)
	{
		$regex = '#^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$#ix';
		return preg_match($regex, $email) == 1 ? true : false;
	}

	/**
     * Checks for valid URL
     *
     * @param  string  $url
     * @return boolean
     */
    public static function url($url)
	{
		$regex = '/^((https?|ftp|rmtp|mms|svn):\/\/)?(www.)?[a-z\-A-Z0-9]+\.[a-z]+/i';
		return preg_match($regex, $url) == 1 ? true : false;
	}

	/**
     * Checks for valid filename
     *
     * @param  string  $filename
     * @return boolean
     */
	public static function filename($filename)
	{
		$options = array('format' => 'a-zA-Z0-9_\-\.', 'min_length' => 2);
		return self::string($filename, $options);
	}
}
