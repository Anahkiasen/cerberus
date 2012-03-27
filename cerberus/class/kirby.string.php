<?php
class str
{
	/*
	########################################
	####### ACTIONS SUR UNE CHAÎNE #########
	########################################
	*/
	
	/* A set of sanitizer methods */
	static function sanitize($string, $type = 'str', $default = NULL)
	{
		$string = stripslashes((string)$string);
		$string = urldecode($string);
		$string = str::utf8($string);

		switch($type)
		{
			case 'int':
				$string = (int)$string;
				break;
			case 'str':
				$string = (string)$string;
				break;
			case 'array':
				$string = (array)$string;
				break;
			case 'nohtml':
				$string = self::unhtml($string);
				break;
			case 'noxml':
				$string = self::unxml($string);
				break;
			case 'enum':
				$string = (in_array($string, array('y', 'n'))) ? $string : $default;
				$string = (in_array($string, array('y', 'n'))) ? $string : 'n';
				break;
			case 'checkbox':
				$string = ($string == 'on') ? 'y' : 'n';
				break;
			case 'plain':
				$string = str::unxml($string);
				$string = str::unhtml($string);
				$string = str::trim($string);
				break;	
			case 'lower':
				$string = str::lower($string);
				break;
			case 'upper':
				$string = str::upper($string);
				break;
			case 'words':
				$string = str::sanitize($string, 'plain');
				$string = preg_replace('/[^\pL]/u', ' ', $string);
			case 'tags':
				$string = str::sanitize($string, 'plain');
				$string = preg_replace('/[^\pL\pN]/u', ' ', $string);
				$string = str::trim($string);
			case 'nobreaks':
				$string = str_replace('\n','',$string);
				$string = str_replace('\r','',$string);
				$string = str_replace('\t','',$string);
				break;
			case 'urlify':
				$string = self::slugify($string);
				break;
			case 'filename':
				$string = f::save_name($string);
				break;
		}

		return is_array($string) ? $string : trim($string);
	}
	
	// Better alternative for explode()
	/* It takes care of removing empty values and it has a built-in way to skip values which are too short.  */
	static function split($string, $separator = ',', $length = 1)
	{
		if(is_array($string)) return $string;
		
		$string = trim($string, $separator);
		$parts	= explode($separator, $string);
		$out	= array();
		
		foreach($parts as $p)
		{
			$p = trim($p);
			if(str::length($p) > 0 && str::length($p) >= $length) $out[] = $p;
		}
		
		return $out;
	}	
		
	/* A switch to display either one or the other string dependend on a counter */
	static function plural($count, $many, $one, $zero = NULL)
	{
		if($count == 1) return $one;
		else if($count == 0 && !empty($zero)) return $zero;
		else return $many;
	}
	
	/* Remplace une chaîne par une autre */
	static function toggle($string, $foo, $bar)
	{
		return ($string == $foo) ? $bar : $foo;
	}
	
	/* A more brutal way to trim.  */
	static function trim($string)
	{
		$string = preg_replace('/\s\s+/u', ' ', $string);
		return trim($string);
	}
	
	/* Tronque une chaîne $string après $count $mode (word/sentence/default:characters) et ajoute $trailing à la fin */
	static function truncate($string, $count = 255, $mode = NULL, $removeHTML = true, $trailing = NULL)
	{
		if($removeHTML) $string = strip_tags($string);
		$string = str::trim($string);
		
		switch($mode)
		{			
			case 'word':
				preg_match('/^([^.!?\s]*[\.!?\s]+){0,'. $count .'}/', strip_tags($string), $excerpt);
				return $excerpt[0].$trailing;
				break;
				
			case 'sentence':
				preg_match('/^([^.!?]*[\.!?]+){0,'. $count .'}/', strip_tags($string), $excerpt);
				return $excerpt[0].$trailing;
				break;
				
			case 'character':
			default:
				$string = str_replace("\n", ' ', $string);
				if(str::length($string) <= $count) return $string;
				return ($count == 0) ? $string : substr($string, 0, strrpos(substr($string, 0, $count), ' ')).$trailing;
				break;
		}
	}
	
	/* Shortens a string by cutting out chars in the middle */
	static function cutout($str, $length, $rep = '…')
	{
		$strlength = str::length($str);
		if($length >= $strlength) return $str;

		// Calc how much we have to cut off
		$cut	= (($strlength+str::length($rep)) - $length);

		// Divide it to cut left and right from the center
		$cutp = round($cut/2);

		// Get the center of the string
		$strcenter = round($strlength/2);

		// Get the start of the cut
		$strlcenter = ($strcenter-$cutp);

		// Get the end of the cut
		$strrcenter = ($strcenter+$cutp);

		// Cut and glue
		return str::substr($str, 0, $strlcenter) . $rep . str::substr($str, $strrcenter);
	}
	
	/* Parses a string by a set of available methods */
	static function parse($string, $mode = 'json')
	{
		if(is_array($string)) return $string;

		switch($mode)
		{
			case 'csv':
				$result = explode("\r", $string);
				if(count($result == 1)) $result = explode("\n", $string);
				foreach($result as $key => $value)
				{
					$val = explode("\t", $value);
					if(sizeof($val) == 1) $val = explode(';', $value);
					$result[$key] = $val;
				}
				break;
			case 'json':
				$result = (array)@json_decode($string, true);
				break;
			case 'xml':
				$result = x::parse($string);
				break;
			case 'url':
				$result = (array)@parse_url($string);
				break;
			case 'query':
				if(url::has_query($string))
				{
					$string = self::split($string, '?');
					$string = a::last($string);
				}
				@parse_str($string, $result);
				break;
			case 'php':
				$result = @unserialize($string);
				break;
			default:
				$result = $string;
				break;
		}

		return $result;
	}
			
	/**** Force a number to be in the 00 format */
	static function twonum($number)
	{
		return str_pad($number, 2, "0", STR_PAD_LEFT);
	}


	/* Convert a string to a safe version */
	static function slugify($text, $accents = false)
	{
		$foreign = array
		(
			'/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ|А/' => 'A',
			'/à|á|â|ã|ä|å|ǻ|ā|ă|ą|ǎ|ª|а/' => 'a',
			'/È|É|Ê|Ë/' => 'E',
			'/è|é|ê|ë/' => 'e',
			'/Ì|Í|Î|Ï/' => 'I',
			'/ì|í|î|ï/' => 'i',
			'/Ò|Ó|Ô|Õ|Ö|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ø|Ǿ|О/' => 'O',
			'/ò|ó|ô|õ|ö|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|о/' => 'o',
			'/Ù|Ú|Û|Ü/' => 'U',
			'/ù|ú|û|ü/' => 'u',
			'/Ç/' => 'C',
			'/ç/' => 'c',
			'/Ñ/' => 'N',
			'/Œ/' => 'OE',
			'/œ/' => 'oe',
			'/Ý/' => 'Y',
			'/Þ/' => 'B',
			'/ß/' => 's',
			'/Š/' => 'S',
			'/š/' => 's',
			'/Ž/' => 'Z',
			'/ž/' => 'z',
			'/æ/' => 'ae'
		);
		
		$text = preg_replace(array_keys($foreign), array_values($foreign), $text);
			if($accents == true) return $text;
		
		$text = preg_replace('![^a-z0-9_]!i', '-', $text);
		$text = preg_replace('/-+/', '-', $text);
		$text = trim($text, '-');
		$text = str::lower($text);
		return $text;
	}
	
	/**** Normalise les accents d'une chaîne */
	static function slugify_accents($text)
	{
		return self::slugify($text, true);
	}
	
	/*
	########################################
	######### INFOS SUR UNE CHAÎNE #########
	########################################
	*/
	
	/**
	 * Find one or more needles in one or more haystacks
	 * 
	 * Also avoid the retarded counter-intuitive original
	 * strpos syntax that makes you put haystack before needle
	 * 
	 * @param mixed		$needle	The needle(s) to search for
	 * @param mixed		$haystack The haystack(s) to search in
	 * @param boolean	$absolute If true all the needle(s) need to be found in all the haystack(s), otherwise one found is enough
	 * @param boolean	$case_sensitive Whether the function is case sensitive or not
	 * @return boolean	Found or not
	 */
	static function find($needle, $haystack, $absolute = FALSE, $case_sensitive = FALSE)
	{
		if(is_array($needle))
		{
			$found = 0;
			foreach($needle as $need) if(self::find($need, $haystack, $absolute, $case_sensitive)) $found++;
			return ($absolute) ? count($needle) == $found : $found > 0;
		}
		elseif(is_array($haystack))
		{
			$found = 0;
			foreach($haystack as $hay) if(self::find($needle, $hay, $absolute, $case_sensitive)) $found++;
			return ($absolute) ? count($haystack) == $found : $found > 0;
		}
		else
		{
			if(!$case_sensitive)
			{
				$haystack = strtolower($haystack);
				$needle = strtolower($needle);
			}
			
			// Simple strpos
			$pos = strpos($haystack, $needle);
			if($pos === false) return FALSE;
			else return TRUE;
		}
	}
	
	/**
	 * Displays the value of a boolean, for debugging
	 * 
	 * @param boolean	$boolean The boolean to display
	 * @return string	TRUE or FALSE
	 */
	static function boolprint($boolean)
	{
		return $boolean ? 'TRUE' : 'FALSE';
	}
	
	/*
	########################################
	########### CREER UNE CHAÎNE ###########
	########################################
	*/
	
	/**
	 * Displays a picture and ensures there is always an alt tag
	 * 
	 * @param string	$src The source of the image
	 * @param string	$alt The alternative text
	 * @param array 	$attr The picture attributes
	 * @return string	The <img> tag
	 */
	static function img($src, $alt = NULL, $attr = NULL)
	{
		$alt = $alt ? $alt : f::filename($src);
		if(!is_array($attr)) $attributes = 'src="' .$src. '" alt="' .$alt. '" '.$attr;
		else
		{
			$attributes = NULL;
			$attr['src'] = $src;
			$attr['alt'] = $alt;
			foreach($attr as $key => $value)
				if(!empty($value)) $attributes .= $key. '="' .$value. '" ';
		}	
		return '<img ' .trim($attributes). ' />';
	}
	
  /**
    * Creates a link tag
    *
    * @param  string  $link The URL
    * @param  string  $text Specify a text for the link tag. If false the URL will be used
    * @param  string  $attr The link tag attributes
    * @return string  
    */  
	static function link($link, $text = false, $attr = NULL)
	{
		$text = ($text) ? $text : $link;
		if(!is_array($attr)) $attributes = 'href="' .$link. '" '.$attr;
		else
		{
			$attributes = NULL;
			$attr['href'] = $link;
			if(!isset($attr['title'])) 
				$attr['title'] = self::unhtml($text);
			
			foreach($attr as $key => $value)
				if(!empty($value)) $attributes .= $key. '="' .$value. '" ';
		}	
		return '<a ' .trim($attributes). '>' . str::html($text) . '</a>';
	}
	
	/**** Utilise la fonction link en combinisaison avec url::rewrite() */
	static function slink($link, $text = NULL, $params = array(), $attr = NULL)
	{
		$page = meta::page($link);
		$page = a::get($page, 'titre');
		if(!isset($attr['title'])) $attr['title'] = $page;
		
		$link = url::rewrite($link, $params);
		return self::link($link, $text, $attr);
	}
	
	/* Generates a random string */
	static function random($length = false)
	{
		$length = ($length) ? $length : rand(10,20);
		$chars	= range('a','z');
		$num	= range(0,9);
		$pool	 = array_merge($chars, $num);
		
		$string = '';
		for($x = 0; $x < $length; $x++)
		{
			shuffle($pool);
			$string .= current($pool);
		}
		return $string;
	}
		
	/* Creates an encoded email address, including proper html-tags */
	static function email($email, $text = false, $title = false, $class = false)
	{
		if(empty($email)) return false;
		$email = 	(string)$email;
		$string = 	(empty($text)) ? $email : $text;
		$email = 	self::encode($email, 3);

		if(!empty($class)) $class = ' class="'.$class.'"';
		if(!empty($title)) $title = ' title="'.html($title).'"';

		return '<a'.$title.$class.' href="mailto:'.$email.'">' .self::encode($string, 3). '</a>';
	}
	
	/**** Encode des accents en HTML sans toucher aux tags */
	static function accents($string, $reverse = false)
	{
		$table = array(
		'ç' => '&ccedil;',
		'Ç' => '&Ccedil;',
		'é' => '&eacute;',
		'è' => '&egrave;',
		'ê' => '&ecirc;',
		'ë' => '&euml;',
		'É' => '&Eacute;',
		'È' => '&Egrave;',
		'Ê' => '&Ecirc;',
		'Ë' => '&Euml;',
		'á' => '&aacute;',
		'à' => '&agrave;',
		'â' => '&acirc;',
		'ä' => '&auml;',
		'Á' => '&Aacute;',
		'À' => '&Agrave;',
		'Â' => '&Acirc;',
		'Ä' => '&Auml;',
		'í' => '&iacute;',
		'ì' => '&igrave;',
		'î' => '&icirc;',
		'ï' => '&iuml;',
		'Í' => '&Iacute,',
		'Ì' => '&Igrave;',
		'Î' => '&Icirc;',
		'Ï' => '&Iuml;',
		'ó' => '&oacute;',
		'ò' => '&ograve;',
		'ô' => '&ocirc;',
		'ö' => '&ouml;',
		'Ó' => '&Oacute;',
		'Ò' => '&Ograve;',
		'Ô' => '&Ocirc;',
		'Ö' => '&Ouml;',
		'ú' => '&uacute;',
		'ù' => '&ugrave;',
		'û' => '&ucirc;',
		'ü' => '&uuml;',
		'Ú' => '&Uacute;',
		'Ù' => '&Ugrave;',
		'Û' => '&Ucirc;',
		'Ü' => '&Uuml;',
		'œ' => '&oelig;',
		'«' => '&laquo;',
		'»' => '&raquo;',
		'€' => '&euro;',
		'©' => '&copy;',
		'•' => '&bull;',
		'’' => '\'',
		'&nbsp;' => ' ');
		
		if(!$reverse) return strtr($string, $table);
		else
		{
			foreach($table as $ascii => $html)
				if($html != ' ' and $ascii != '’')
					$string = str_replace($html, $ascii, $string);
			
			return $string;
		}
	}

	/*
	########################################
	######## AFFICHAGE D'UNE CHAINE ########
	########################################
	*/

	static function alert($message, $type = 'info')
	{
		return '<p class="infoblock alert alert-' .$type. '">' .$message. '</p>';
	}
	
	/* Raccourcis d'alertes */
	static function display($message, $type = 'info')
	{
		echo self::alert($message, $type);
	}
	static function translate($message, $default = NULL, $type = 'info')
	{
		echo self::alert(l::get($message, $default), $type);
	}
	
	/* Affiche un status */
	static function status($bool, $message_success, $message_error)
	{
		$message = $bool ? $message_success : $message_error;
		$type = $bool ? 'success' : 'error';
		echo self::alert($message, $type);
	}

	/*
	########################################
	################# UTF8 #################
	########################################
	*/

	/* Converts a string to UTF-8 */
	static function utf8($string)
	{
		$encoding = mb_detect_encoding($string,'UTF-8, ISO-8859-1, GBK');
		return ($encoding != 'UTF-8') ? iconv($encoding,'utf-8',$string) : $string;
	}

	/* An UTF-8 safe version of substr() */
	static function substr($str, $start, $end = NULL)
	{
		return mb_substr($str, $start, ($end == null) ? mb_strlen($str, 'UTF-8') : $end, 'UTF-8');
	}
	
	/* An UTF-8 safe version of ucwords() */
	static function ucwords($str)
	{
		return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
	}

	/* An UTF-8 safe version of ucfirst() */
	static function ucfirst($str)
	{
		return str::upper(str::substr($str, 0, 1)) . str::substr($str, 1);
	}
	
	/* An UTF-8 safe version of strtolower() */
	static function lower($str)
	{
		return mb_strtolower($str, 'UTF-8');
	}

	/* An UTF-8 safe version of strotoupper() */
	static function upper($str)
	{
		return mb_strtoupper($str, 'UTF-8');
	}

	/* An UTF-8 safe version of strlen() */
	static function length($str)
	{
		return mb_strlen($str, 'UTF-8');
	}


	
	/*
	########################################
	############### NON TRIE ###############
	########################################
	*/

	/* Converts a string to a xml-safe string. Converts it to html-safe first and then it will replace html entities to xml entities */
	static function xml($text, $html = true)
	{
		// convert raw text to html safe text
		if($html) $text = self::html($text);
		
		// convert html entities to xml entities
		return strtr($text, self::entities());
	}

	/* Removes all xml entities from a string */
	static function unxml($string)
	{
		// flip the conversion table
		$table = array_flip(self::entities());

		// convert html entities to xml entities
		return strip_tags(strtr($string, $table));
	}
	
	/* Converts a string to a html-safe string */
	static function html($string, $keep_html = true)
	{
		if($keep_html)
		{
			return
			stripslashes(
				implode(
					'', 
					preg_replace(
						'/^([^<].+[^>])$/e',
						"htmlentities('\\1', ENT_COMPAT, 'utf-8')",
						preg_split('/(<.+?>)/',
						$string,
						-1,
						PREG_SPLIT_DELIM_CAPTURE))));
		}
		else return htmlentities($string, ENT_COMPAT, 'utf-8');
	}
	
	/* Removes all html tags and encoded chars from a string */
	static function unhtml($string)
	{
			$string = strip_tags($string);
			return html_entity_decode($string, ENT_COMPAT, 'utf-8');
	}
	
	/* Encode a string (used for email addresses) */
	static function encode($string)
	{
		$encoded = NULL;
		$length	= str::length($string);
		for($i = 0; $i < $length; $i++)
			$encoded .= (rand(1, 2) == 1) ? '&#' . ord($string[$i]) . ';' : '&#x' . dechex(ord($string[$i])) . ';';

		return $encoded;
	}

	/* Shortens a string and adds an ellipsis if the string is too long */
	static function short($string, $chars, $rep = '…')
	{
		if(str::length($string) <= $chars) return $string;
		$string = self::substr($string, 0, ($chars - str::length($rep)));
		$punctuation = '.!?:;,-';
		$string = (strspn(strrev($string), $punctuation) != 0) ? substr($string, 0, -strspn(strrev($string), $punctuation)) : $string;
		return $string . $rep;
	}

	/* Shortens an URL */
	static function shorturl($url, $chars = false, $base = false, $rep = '…')
	{
		return url::short($url, $chars, $base, $rep);
	}

	/* Checks if a str contains another string */
	static function contains($str, $needle)
	{
		return strstr($str, $needle);
	}

	// preg_match sucks! This tries to make it more convenient
	/* Search $regex in $string, returns the $get entry from the result array, or $placeholder if not found */
	static function match($string, $regex, $get = false, $placeholder = false)
	{
		$match = @preg_match($regex, $string, $array);
		if(!$match) return false;
		if($get === false) return $array;
		return a::get($array, $get, $placeholder);
	}

	


	

	/* A better way to strip slashes */
	static function stripslashes($string)
	{
		if(is_array($string)) return $string;
		return (get_magic_quotes_gpc()) ? stripslashes(stripslashes($string)) : $string;
	}
	
	/* An internal store for a html entities translation table */
	static function entities()
	{
			return array(
				'&nbsp;' => '&#160;', '&iexcl;' => '&#161;', '&cent;' => '&#162;', '&pound;' => '&#163;', '&curren;' => '&#164;', '&yen;' => '&#165;', '&brvbar;' => '&#166;', '&sect;' => '&#167;',
				'&uml;' => '&#168;', '&copy;' => '&#169;', '&ordf;' => '&#170;', '&laquo;' => '&#171;', '&not;' => '&#172;', '&shy;' => '&#173;', '&reg;' => '&#174;', '&macr;' => '&#175;',
				'&deg;' => '&#176;', '&plusmn;' => '&#177;', '&sup2;' => '&#178;', '&sup3;' => '&#179;', '&acute;' => '&#180;', '&micro;' => '&#181;', '&para;' => '&#182;', '&middot;' => '&#183;',
				'&cedil;' => '&#184;', '&sup1;' => '&#185;', '&ordm;' => '&#186;', '&raquo;' => '&#187;', '&frac14;' => '&#188;', '&frac12;' => '&#189;', '&frac34;' => '&#190;', '&iquest;' => '&#191;',
				'&Agrave;' => '&#192;', '&Aacute;' => '&#193;', '&Acirc;' => '&#194;', '&Atilde;' => '&#195;', '&Auml;' => '&#196;', '&Aring;' => '&#197;', '&AElig;' => '&#198;', '&Ccedil;' => '&#199;',
				'&Egrave;' => '&#200;', '&Eacute;' => '&#201;', '&Ecirc;' => '&#202;', '&Euml;' => '&#203;', '&Igrave;' => '&#204;', '&Iacute;' => '&#205;', '&Icirc;' => '&#206;', '&Iuml;' => '&#207;',
				'&ETH;' => '&#208;', '&Ntilde;' => '&#209;', '&Ograve;' => '&#210;', '&Oacute;' => '&#211;', '&Ocirc;' => '&#212;', '&Otilde;' => '&#213;', '&Ouml;' => '&#214;', '&times;' => '&#215;',
				'&Oslash;' => '&#216;', '&Ugrave;' => '&#217;', '&Uacute;' => '&#218;', '&Ucirc;' => '&#219;', '&Uuml;' => '&#220;', '&Yacute;' => '&#221;', '&THORN;' => '&#222;', '&szlig;' => '&#223;',
				'&agrave;' => '&#224;', '&aacute;' => '&#225;', '&acirc;' => '&#226;', '&atilde;' => '&#227;', '&auml;' => '&#228;', '&aring;' => '&#229;', '&aelig;' => '&#230;', '&ccedil;' => '&#231;',
				'&egrave;' => '&#232;', '&eacute;' => '&#233;', '&ecirc;' => '&#234;', '&euml;' => '&#235;', '&igrave;' => '&#236;', '&iacute;' => '&#237;', '&icirc;' => '&#238;', '&iuml;' => '&#239;',
				'&eth;' => '&#240;', '&ntilde;' => '&#241;', '&ograve;' => '&#242;', '&oacute;' => '&#243;', '&ocirc;' => '&#244;', '&otilde;' => '&#245;', '&ouml;' => '&#246;', '&divide;' => '&#247;',
				'&oslash;' => '&#248;', '&ugrave;' => '&#249;', '&uacute;' => '&#250;', '&ucirc;' => '&#251;', '&uuml;' => '&#252;', '&yacute;' => '&#253;', '&thorn;' => '&#254;', '&yuml;' => '&#255;',
				'&fnof;' => '&#402;', '&Alpha;' => '&#913;', '&Beta;' => '&#914;', '&Gamma;' => '&#915;', '&Delta;' => '&#916;', '&Epsilon;' => '&#917;', '&Zeta;' => '&#918;', '&Eta;' => '&#919;',
				'&Theta;' => '&#920;', '&Iota;' => '&#921;', '&Kappa;' => '&#922;', '&Lambda;' => '&#923;', '&Mu;' => '&#924;', '&Nu;' => '&#925;', '&Xi;' => '&#926;', '&Omicron;' => '&#927;',
				'&Pi;' => '&#928;', '&Rho;' => '&#929;', '&Sigma;' => '&#931;', '&Tau;' => '&#932;', '&Upsilon;' => '&#933;', '&Phi;' => '&#934;', '&Chi;' => '&#935;', '&Psi;' => '&#936;',
				'&Omega;' => '&#937;', '&alpha;' => '&#945;', '&beta;' => '&#946;', '&gamma;' => '&#947;', '&delta;' => '&#948;', '&epsilon;' => '&#949;', '&zeta;' => '&#950;', '&eta;' => '&#951;',
				'&theta;' => '&#952;', '&iota;' => '&#953;', '&kappa;' => '&#954;', '&lambda;' => '&#955;', '&mu;' => '&#956;', '&nu;' => '&#957;', '&xi;' => '&#958;', '&omicron;' => '&#959;',
				'&pi;' => '&#960;', '&rho;' => '&#961;', '&sigmaf;' => '&#962;', '&sigma;' => '&#963;', '&tau;' => '&#964;', '&upsilon;' => '&#965;', '&phi;' => '&#966;', '&chi;' => '&#967;',
				'&psi;' => '&#968;', '&omega;' => '&#969;', '&thetasym;' => '&#977;', '&upsih;' => '&#978;', '&piv;' => '&#982;', '&bull;' => '&#8226;', '&hellip;' => '&#8230;', '&prime;' => '&#8242;',
				'&Prime;' => '&#8243;', '&oline;' => '&#8254;', '&frasl;' => '&#8260;', '&weierp;' => '&#8472;', '&image;' => '&#8465;', '&real;' => '&#8476;', '&trade;' => '&#8482;', '&alefsym;' => '&#8501;',
				'&larr;' => '&#8592;', '&uarr;' => '&#8593;', '&rarr;' => '&#8594;', '&darr;' => '&#8595;', '&harr;' => '&#8596;', '&crarr;' => '&#8629;', '&lArr;' => '&#8656;', '&uArr;' => '&#8657;',
				'&rArr;' => '&#8658;', '&dArr;' => '&#8659;', '&hArr;' => '&#8660;', '&forall;' => '&#8704;', '&part;' => '&#8706;', '&exist;' => '&#8707;', '&empty;' => '&#8709;', '&nabla;' => '&#8711;',
				'&isin;' => '&#8712;', '&notin;' => '&#8713;', '&ni;' => '&#8715;', '&prod;' => '&#8719;', '&sum;' => '&#8721;', '&minus;' => '&#8722;', '&lowast;' => '&#8727;', '&radic;' => '&#8730;',
				'&prop;' => '&#8733;', '&infin;' => '&#8734;', '&ang;' => '&#8736;', '&and;' => '&#8743;', '&or;' => '&#8744;', '&cap;' => '&#8745;', '&cup;' => '&#8746;', '&int;' => '&#8747;',
				'&there4;' => '&#8756;', '&sim;' => '&#8764;', '&cong;' => '&#8773;', '&asymp;' => '&#8776;', '&ne;' => '&#8800;', '&equiv;' => '&#8801;', '&le;' => '&#8804;', '&ge;' => '&#8805;',
				'&sub;' => '&#8834;', '&sup;' => '&#8835;', '&nsub;' => '&#8836;', '&sube;' => '&#8838;', '&supe;' => '&#8839;', '&oplus;' => '&#8853;', '&otimes;' => '&#8855;', '&perp;' => '&#8869;',
				'&sdot;' => '&#8901;', '&lceil;' => '&#8968;', '&rceil;' => '&#8969;', '&lfloor;' => '&#8970;', '&rfloor;' => '&#8971;', '&lang;' => '&#9001;', '&rang;' => '&#9002;', '&loz;' => '&#9674;',
				'&spades;' => '&#9824;', '&clubs;' => '&#9827;', '&hearts;' => '&#9829;', '&diams;' => '&#9830;', '&quot;' => '&#34;', '&amp;' => '&#38;', '&lt;' => '&#60;', '&gt;' => '&#62;', '&OElig;' => '&#338;',
				'&oelig;' => '&#339;', '&Scaron;' => '&#352;', '&scaron;' => '&#353;', '&Yuml;' => '&#376;', '&circ;' => '&#710;', '&tilde;' => '&#732;', '&ensp;' => '&#8194;', '&emsp;' => '&#8195;',
				'&thinsp;' => '&#8201;', '&zwnj;' => '&#8204;', '&zwj;' => '&#8205;', '&lrm;' => '&#8206;', '&rlm;' => '&#8207;', '&ndash;' => '&#8211;', '&mdash;' => '&#8212;', '&lsquo;' => '&#8216;',
				'&rsquo;' => '&#8217;', '&sbquo;' => '&#8218;', '&ldquo;' => '&#8220;', '&rdquo;' => '&#8221;', '&bdquo;' => '&#8222;', '&dagger;' => '&#8224;', '&Dagger;' => '&#8225;', '&permil;' => '&#8240;',
				'&lsaquo;' => '&#8249;', '&rsaquo;' => '&#8250;', '&euro;' => '&#8364;'
			);
	}
}
?>