<?php
class str
{
	/*
	########################################
	####### ACTIONS SUR UNE CHAÎNE #########
	########################################
	*/
	
	// Nettoie une chaîne selon un mode donné
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
	
	// Explose une string en array
	static function split($string, $separator = ',', $length = 1)
	{
		if(is_array($string)) return $string;
		
		$string = trim($string, $separator);
		$parts  = explode($separator, $string);
		$out    = array();
		
		foreach($parts as $p)
		{
			$p = trim($p);
			if(str::length($p) > 0 && str::length($p) >= $length) $out[] = $p;
		}
		
		return $out;
	}	
		
	// Met une chaîne au pluriel ou singulier (ou absence de)
	static function plural($count, $many, $one, $zero = NULL)
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
	
	// Tronque une chaîne $string après $count $mode (word/sentence/default:characters) et ajoute $trailing à la fin
	static function truncate($string, $count = 255, $mode = NULL, $trailing = NULL)
	{
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
				
			default:
				$count -= str::length($trailing);
				if(str::length($string) > $count) return mb_substr($string, 0, $count).$trailing;
				else return $string;
				break;
		}
	}
	
	// Supprime tout HTML d'une chaîne
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
	
	// Met un chiffre à deux numéros
	static function twonum($number)
	{
		return str_pad($number, 2, "0", STR_PAD_LEFT);
	}
	
	// Transforme une chaîne en HTML valide
	static function html($string, $keep_html = true)
	{
		if($keep_html)
			return
			stripslashes(
				implode('',
					preg_replace('/^([^<].+[^>])$/e', "htmlentities('\\1', ENT_COMPAT, 'utf-8')", preg_split('/(<.+?>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE))));

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
	static function wrap($balise, $texte = NULL)
	{
		$var = array('content' => $texte);
		return zenPHP($balise.'["{content}"]', $var);
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
	
	// Utilise la fonction link en combinisaison avec url::rewrite()
	static function slink($link, $text = NULL, $params = NULL, $attr = NULL)
	{
		$link = url::rewrite($link, $params);
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
	static function email($email, $text = false, $title = false, $class = false)
	{
		if(empty($email)) return false;
		$email = 	(string)$email;
		$string = 	(empty($text)) ? $email : $text;
		$email = 	self::encode($email, 3);

		if(!empty($class)) $class = ' class="'.$class.'"';
		if(!empty($title)) $title = ' title="'.html($title).'"';

		return '<a'.$title.$class.' href="mailto:'.$email.'">'.self::encode($string, 3).'</a>';
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
	######## AFFICHAGE D'UNE CHAINE ########
	########################################
	*/

	static function alert($message, $type = 'info')
	{
		return '<p class="infoblock alert alert-' .$type. '">' .$message. '</p>';
	}
	
	// Raccourcis d'alertes
	static function display($message, $type = 'info')
	{
		echo self::alert($message, $type);
	}
	static function translate($message, $default = NULL, $type = 'info')
	{
		echo self::alert(l::get($message, $default), $type);
	}
	
	// Affiche un status
	static function status($bool, $message_success, $message_error)
	{
		$message = $bool ? $message_success : $message_error;
		$type = $bool ? 'success' : 'error';
		echo self::alert($message, $type);
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
	
	static function unhtml($string)
	{
	    $string = strip_tags($string);
	    return html_entity_decode($string, ENT_COMPAT, 'utf-8');
	}
	
	static function encode($string)
	{
		$encoded = '';
		$length	= str::length($string);
		for($i = 0; $i < $length; $i++)
			$encoded .= (rand(1,2)==1) ? '&#' . ord($string[$i]) . ';' : '&#x' . dechex(ord($string[$i])) . ';';

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
	
	static function substr($str, $start, $end = NULL)
	{
		return mb_substr($str, $start, ($end == null) ? mb_strlen($str, 'UTF-8') : $end, 'UTF-8');
	}

	static function contains($str, $needle)
	{
		return strstr($str, $needle);
	}

	static function match($string, $preg, $get = false, $placeholder = false)
	{
		$match = preg_match($preg, $string, $array);
		if(!$match) return false;
		if(!$get) return $array;
		return a::get($array, $get, $placeholder);
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