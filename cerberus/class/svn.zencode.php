<?php
function zenPHP($code, $var = array())
{
	$code = explode('>', $code);
	$domarray = array();
	foreach($code as $item)
	{
		$sub = explode('+', trim($item));
		foreach($sub as $key => $s)
		{
			$s = trim($s);
			$domarray[] = array('type' => ($key ? '+' : '>'), 'data' => $s);
		}
	}

	$html = zen_parse($domarray, 0, $var);
	return $html;
}

function zen_parse($code, $index, $var)
{
	$t = 'dom';
	$c = $code[$index];
	$dom = $c['data'];
	$data = array();
	$i = 0;
	$v = '';
	$content = '';
	while($i < strlen($dom))
	{
		if($dom[$i] == '.')
		{

			$data[$t][] = $v;
			$t = 'class';
			$v = '';
		}
		elseif($dom[$i] == '#')
		{
			$data[$t][] = $v;
			$t = 'id';

			$v = '';
		}
		elseif($dom[$i] == '[')
		{
			$data[$t][] = $v;
			$t = 'params';
			$v = '';
		}
		elseif($dom[$i] == ']')
		{
			$data[$t][] = $v;
			$t = 'E';
			$v = '';
		}
		elseif($dom[$i] == '{')
		{
			while($dom[$i] <> '}')
			{
				$v = $v.$dom[$i++];
			}
			$v = $v.$dom[$i];

		}
		elseif($dom[$i] == '*')
		{
			$data[$t][] = $v;
			$t = 'times';
			$v = '';
		}
		else
			$v = $v.$dom[$i];
		$i++;
	}

	$data[$t][] = $v;
	$times = 1;
	if(isset($data['times'][0]))
		$times = $data['times'][0];
	$rot = 0;
	$html = '';
	while($rot < $times)
	{
		$subhtml = '<'.$data['dom'][0];
		if(isset($data['id']))
			$subhtml .= ' id="'.$data['id'][0].'"';
		if(isset($data['class']))
		{
			$d = '';
			foreach($data['class'] as $cl) $d .= ' '.$cl;
			$subhtml .= ' class="'.trim($d).'"';
		}
		if(isset($data['params']))
		{
			foreach($data['params'] as $pr)
			{
				if(preg_match('/(.*)="(.*)"$/', $pr))
					$subhtml .= ' '.trim($pr);
				else
					$content = ' '.trim($pr, '"');
			}

		}

		$subhtml .= '>'.$content;
		if((isset($code[$index + 1]['type'])) and ($code[$index + 1]['type'] == '>'))
			$subhtml .= zen_parse($code, $index + 1, $var);
		$subhtml .= '</'.$data['dom'][0].'>';
		$subhtml = zen_replace_var($subhtml, $rot, $var);
		$html .= $subhtml;
		$rot++;
	}
	if((isset($code[$index + 1]['type'])) and ($code[$index + 1]['type'] == '+'))
		$html .= zen_parse($code, $index + 1, $var);

	return $html;
}

function zen_replace($matches)
{
	global $var_data_zen, $var_index_zen;
	$match = $matches[1];
	foreach($var_data_zen as $key => $value)
		$$key = $value;

	$match = str_replace('$', $var_index_zen, $match);

	eval("\$data = $".$match.";");

	return $data;
}

function zen_replace_code($matches)
{
	global $var_data_zen, $var_index_zen;
	$match = $matches[1];

	foreach($var_data_zen as $key => $value)
		$$key = $value;

	$index = $var_index_zen;
	$data = eval($match);

	return $data;
}

function zen_replace_var($html, $index, $var)
{
	global $var_data_zen, $var_index_zen;

	$pattern = '/{([\w$\[\]"()]+?)}/i';
	// Matches any template tag
	$callback = "zen_replace";
	$var_data_zen = $var;
	$var_index_zen = $index;

	$html = preg_replace_callback($pattern, $callback, $html);

	$pattern = '/{=(.*)}/i';
	// Matches any template tag
	$callback = "zen_replace_code";

	$html = preg_replace_callback($pattern, $callback, $html);

	$html = preg_replace('/\$/', $index, $html);
	return $html;
}
?>