<?php
class str
{
	/*
	########################################
	####### ACTIONS SUR UNE CHAÎNE #########
	########################################
	*/
	
	// Explose une string en array
	static function split($string, $separator = ',', $taille = 1)
	{
		if(is_array($string)) return $string;
		else
		{
			// 
			$psep	= preg_quote($separator);
			$string = preg_replace('!^' . $psep . '!', '', $string);
			$string = preg_replace('!' . $psep . '$!', '', $string);
	
			$parts 	= explode($separator, $string);
			$out 	= array();
	
			foreach($parts as $p)
			{
				$p = self::trim($p);
				if(!empty($p) && str::length($p) >= $taille) $out[] = $p;
			}
	
			return $out;
		}
	}
	
	// Met une chaîne au pluriel ou singulier (ou absence de)
	static function plural($count, $many, $one, $zero = '')
	{
		if($count == 1) return $one;
		else if($count == 0 && !empty($zero)) return $zero;
		else return $many;
	}
	
	// Remplace une chaîne par une autre
	static function toggle($string, $foo, $bar)
	{
		return ($string == $foo) ? $bar : $foo;
	}
	
	// Trim une string
	static function trim($string)
	{
		$string = preg_replace('/\s\s+/u', ' ', $string);
		return trim($string);
	}
	
	// Supprime tout HTML d'une ch$aine
	static function stripHTML($string)
	{
		$string = str_replace('<br />', PHP_EOL, $string);
		$string = strip_tags($string);
		return html_entity_decode($string, ENT_COMPAT, 'utf-8');
	}
	
	// Parse une chaîne
	static function parse($string, $mode = 'json')
	{
		if(is_array($string)) return $string;

		switch($mode)
		{
			case 'csv':
				$result = explode("\r", $string);
				if(count($result == 1)) $result = explode("\n", $string);
				foreach($result as $key => $value) $result[$key] = explode(';', $value);
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
		
	// Met une chaîne en minuscule
	static function lower($str)
	{
		return mb_strtolower($str, 'UTF-8');
	}

	// Met une chaîne en majuscule
	static function upper($str)
	{
		return mb_strtoupper($str, 'UTF-8');
	}
	
	// Transforme une chaîne en HTML valide
	static function html($string, $keep_html = true)
	{
		if($keep_html)
			return stripslashes(implode('', preg_replace('/^([^<].+[^>])$/e', "htmlentities('\\1', ENT_COMPAT, 'utf-8')", preg_split('/(<.+?>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE))));

		else
			return htmlentities($string, ENT_COMPAT, 'utf-8');
	}

	// Normalise une chaîne
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
	
	// Normalise les accents d'une chaîne
	static function slugify_accents($text)
	{
		return self::slugify($text, true);
	}
	
	// Ajout des balises HTML autour d'une chaîne
	static function wrap($balise, $texte = NULL, $attr = NULL)
	{
		if($attr)
			$attributes = (is_array($attr))
				? a::simplode(array('="', '"'), ' ', $attr)
				: $attr;
		else $attributes = NULL;
	
		if(is_array($balise))
		{
			foreach($balise as $bal => $balattr)
			{
				if(is_numeric($bal))
				{
					$bal = $balattr;
					$balattr = NULL;
				}
				$texte = self::wrap($bal, $texte, $balattr);
			}
		}		
		else $texte = '<' .$balise. ' ' .$attributes. '>' .$texte. '</' .$balise. '>';
		
		return $texte;
	}
	
	/*
	########################################
	######### INFOS SUR UNE CHAÎNE #########
	########################################
	*/
	
	// Trouve une chaîne dans une autre
	static function find($needle, $haystack, $absolute = FALSE, $case_sensitive = FALSE)
	{
		if(is_array($needle))
		{
			$found = 0;
			foreach($needle as $need) if(self::find($need, $haystack)) $found++;
			return ($absolute) ? count($needle) == $found : $found > 0;
		}
		elseif(is_array($haystack))
		{
			$found = 0;
			foreach($haystack as $hay) if(self::find($needle, $hay)) $found++;
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

	// Longueur d'une chaîne
	static function length($str)
	{
		return mb_strlen($str, 'UTF-8');
	}
	
	// Affiche la valeur d'un booléen
	static function boolprint($boolean)
	{
		return ($boolean) ? 'TRUE' : 'FALSE';
	}
	
	/*
	########################################
	########### CREER UNE CHAÎNE ###########
	########################################
	*/
	
	// Affiche une image
	static function img($src, $alt = NULL, $attr = NULL)
	{
		if($attr)
			$attributes = (is_array($attr))
				? a::simplode(array('="', '"'), ' ', $attr)
				: $attr;
		else $attributes = NULL;
	
		$alt = ($alt) ? $alt : pathinfo($src, PATHINFO_FILENAME);
		return '<img src="' .$src. '" alt="' .$alt. '" ' .$attributes. ' />';
	}
	
	// Créer un lien à partir d'une chaîne
	static function link($link, $text = NULL, $attr = NULL)
	{
		if($attr)
			$attributes = (is_array($attr))
				? a::simplode(array('="', '"'), ' ', $attr)
				: $attr;
		else $attributes = NULL;
		
		$text = ($text) ? $text : $link;
		return '<a href="' . $link . '" ' .$attributes. '>' . str::html($text) . '</a>';
	}
	
	// Utilise la fonction link en combinisaison avec rewrite()
	static function slink($link, $text = NULL, $params = NULL, $attr = NULL)
	{
		$link = rewrite($link, $params);
		return self::link($link, $text, $attr);
	}
	
	// Génère une chaîne aléatoire
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
		
	// Créer un lien mailto
	static function email($email, $text = FALSE)
	{
		if(empty($email)) return false;
		$string = (empty($text)) ? $email : $text;
		$email	= self::encode($email, 3);
		return '<a title="' .$email. '" class="email" href="mailto:' .$email. '">' .self::encode($string, 3). '</a>';
	}

	// Encode des accents en HTML sans toucher aux tags
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
	############### NON TRIE ###############
	########################################
	*/

	static function xml($text, $html = true)
	{
		// convert raw text to html safe text
		if($html) $text = self::html($text);
		
		// convert html entities to xml entities
		return strtr($text, self::entities());
	}

	static function unxml($string)
	{
		// flip the conversion table
		$table = array_flip(self::entities());

		// convert html entities to xml entities
		return strip_tags(strtr($string, $table));
	}
	
	static function encode($string)
	{
		$encoded = '';
		$length	= str::length($string);
		for($i = 0; $i<$length; $i++) {
			$encoded .= (rand(1,2)==1) ? '&#' . ord($string[$i]) . ';' : '&#x' . dechex(ord($string[$i])) . ';';
		}
		return $encoded;
	}

	static function short($string, $chars, $rep = '…')
	{
		if(str::length($string) <= $chars) return $string;
		$string = self::substr($string, 0, ($chars - str::length($rep)));
		$punctuation = '.!?:;,-';
		$string = (strspn(strrev($string), $punctuation) != 0) ? substr($string, 0, -strspn(strrev($string), $punctuation)) : $string;
		return $string . $rep;
	}

	static function shorturl($url, $chars = false, $base = false, $rep = '…')
	{
		return url::short($url, $chars, $base, $rep);
	}

	static function cutout($str, $length, $rep = '…')
	{
		$strlength = str::length($str);
		if($length >= $strlength) return $str;

		// calc the how much we have to cut off
		$cut	= (($strlength+str::length($rep)) - $length);

		// divide it to cut left and right from the center
		$cutp = round($cut/2);

		// get the center of the string
		$strcenter = round($strlength/2);

		// get the start of the cut
		$strlcenter = ($strcenter-$cutp);

		// get the end of the cut
		$strrcenter = ($strcenter+$cutp);

		// cut and glue
		return str::substr($str, 0, $strlcenter) . $rep . str::substr($str, $strrcenter);
	}

	static function substr($str, $start, $end = NULL)
	{
		return mb_substr($str, $start, ($end == null) ? mb_strlen($str, 'UTF-8') : $end, 'UTF-8');
	}

	static function contains($str, $needle)
	{
		return strstr($str, $needle);
	}

	static function match($string, $preg, $get = false, $placeholder = false) {
		$match = preg_match($preg, $string, $array);
		if(!$match) return false;
		if(!$get) return $array;
		return a::get($array, $get, $placeholder);
	}

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
			case 'url':
				$string = (v::url($string)) ? $string : NULL;
				break;
			case 'email':
				$string = (v::email($string)) ? $string : NULL;
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
			case 'url':
				$string = self::slugify($string);
				break;
			case 'filename':
				$string = f::save_name($string);
				break;
		}

		return trim($string);

	}

	static function ucwords($str)
	{
		return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
	}

	static function ucfirst($str)
	{
		return str::upper(str::substr($str, 0, 1)) . str::substr($str, 1);
	}

	static function utf8($string)
	{
		$encoding = mb_detect_encoding($string,'UTF-8, ISO-8859-1, GBK');
		return ($encoding != 'UTF-8') ? iconv($encoding,'utf-8',$string) : $string;
	}

	static function stripslashes($string)
	{
		if(is_array($string)) return $string;
		return (get_magic_quotes_gpc()) ? stripslashes(stripslashes($string)) : $string;
	}
}
?>