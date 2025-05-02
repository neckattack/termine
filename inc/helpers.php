<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * helpers.php
 * Useful helper functions
 */


/**
 * Returns a formatted time string
 * @param  string $time The time
 * @param  array  $format (optional) An array with the format for the dates. Default value is YYYY-MM-DD
 * @return string The string for Javascript date picker
 */
function formatTime($time, $format=array("H", "m", "s", ":")) {
	$str = $format[0].$format[3].$format[1];

	if (isset($format[2][0])) {
		$str .= $format[3].$format[2];
	}

	return date($str, $time);
}


/**
 * Returns a formatted date string
 * @param  string $date The date
 * @param  array  $format (optional) An array with the format for the dates. Default value is YYYY-MM-DD
 * @return string The string for Javascript date picker
 */
function formatDate($date, $format=array("Y", "m", "d", "-")) {
	return date($format[0].$format[3].$format[1].$format[3].$format[2], strtotime($date));
}


/**
 * Returns a date string formatted for the Javascript date picker
 * @param  array $format (optional) An array with the format for the dates. Default value is YYYY-MM-DD
 * @return string The string for Javascript date picker
 */
function getJSDateString($format=array("Y", "m", "d", "-")) {
	function retDate($str) {
		return str_repeat($str, 2);
	}

	// Outputs e.g. YYYY-MM-DD
	return strtolower(retDate($format[0]).$format[3].retDate($format[1]).$format[3].retDate($format[2]));
}


/**
 * Returns a date string formatted for the MySQL DATE_FORMAT() function
 * @param  array $format (optional) An array with the format for the dates. Default value is YYYY-MM-DD
 * @return string The string for the MySQL DATE_FORMAT() function
 */
function getMySQLDateString($format=array("Y", "m", "d", "-")) {
	// Outputs e.g. YYYY-MM-DD
	return "%".$format[0].$format[3]."%".$format[1].$format[3]."%".$format[2];
}


/**
 * Takes a date string and returns the MySQL date equivalent of it
 * @param  string $dateStr    The date string formatted according to $format
 * @param  array  $userFormat The format in which the date string is formatted
 * @return string $mysqlDate  The date formatted in MySQL format (YYYY-MM-DD)
 */
function getMySQLDate($dateStr, $userFormat=false) {
	global $config;
	$mysqlFormat = array("Y", "m", "d", "-");		// The MySQL date format

	if (!$userFormat) {
		$userFormat = $config["dateFormat"];
	}

	$valArr = explode($userFormat[3], $dateStr);	// Explode the user format using the delimiter

	if (count($valArr) < 2) return;

	// Lookup where year, month and day values are in the user formatted string
	$formatTemp = array_flip($userFormat);
	// The year part of the string
	$keyYear  = (isset($formatTemp["Y"])) ? $formatTemp["Y"] : ( (isset($formatTemp["y"])) ? $formatTemp["y"] : false );	// Y or y
	// The month part of the string
	$keyMonth = (isset($formatTemp["m"])) ? $formatTemp["m"] : ( (isset($formatTemp["n"])) ? $formatTemp["n"] : false );	// m or n
	// The day part of the string
	$keyDay   = (isset($formatTemp["d"])) ? $formatTemp["d"] : ( (isset($formatTemp["j"])) ? $formatTemp["j"] : false );	// d or j

	if ($keyYear === false || $keyMonth === false || $keyDay === false) return false;

	// Re-arrange the user date
	$mysqlDate = date("Y-m-d", strtotime($valArr[$keyYear]."-".$valArr[$keyMonth]."-".$valArr[$keyDay]));

	// return the new date string
	return $mysqlDate;
}


/**
 * Output an array as JSON
 * @param  array $array  The array to be send
 * @return void
 */
function JSON($array) {
	//header('Content-type: application/x-json; charset=utf-8');
	header('Content-type: application/json');
	echo json_encode($array);
}


/**
 * Output an HTTP header
 * @param  int   $code  The HTTP status code
 * @param  bool  $die   Cancel any further output
 * @return void
 */
function setHeader($code, $die=true) {
	$header = $_SERVER["SERVER_PROTOCOL"]." ";		// HTTP/1.0 or HTTP/1.1

	// The differnt status codes
	switch ($code) {
		case 401: $header .= "401 Unauthorized"; break;
		case 403: $header .= "403 Forbidden"; break;
		case 404: $header .= "404 Not Found"; break;
		case 412: $header .= "412 Precondition Failed"; break;
		default : $header .= "200 OK"; break;
	}

	header($header);
	if ($die === true) {
		die();
	}
}


/**
 * Output a Pagination
 * @param  int    $max    The maximum number of pages
 * @param  int    $cur    The current page
  * @return void
 */
function buildPagination($max, $cur=1) {
	if ($max < 2) return;

	/*
		show always 1
		show always cur
		show always -2 and +2
		show always last page
		show 9 pages plus forward and back (so 11 elements total)
	*/

	$begin  = array(1);
	$middle = array();
	$end    = array();

	$back = max(1, ($cur-1));							// Back (-1)
	$forw = min($max, ($cur+1));						// Forward (+1)

	if ($max > 2) {
		$midStart = max(2, $cur-3);						// Middle part: minimum current page - 3
		$midEnd   = min($max-1, $cur+3);				// Middle part: maximum current page + 3

		$middle   = range($midStart, $midEnd);			// Pages in the middle part (+-3 around the current page)
	}
	if ($max > 1) {
		$end      = array($max);						// Total count of pages
	}


	$show = array_merge($begin, $middle, $end);
	?>
	<ul class="pagination<?=$pos?>">
		<li class="back"><a href="<?=buildQuery(array("page"=>$back))?>">&laquo;</a></li>
	<?php
	foreach ($show AS $i) {
		if ($i < 1) continue;
		if ($i > $max) continue;
		$current = ($cur == $i) ? ' class="cur"' : "";
	?>
		<li<?=$current?>><a href="<?=buildQuery(array("page"=>$i))?>"><?=$i?></a></li>
	<?php }?>
		<li class="forward"><a href="<?=buildQuery(array("page"=>$forw))?>">&raquo;</a></li>
	</ul>
	<?php
}


/**
 * Generate a link out of the parameters in a URL
 * Read the existing parameters and replace any & by &amp;
 * @param   array   $additional  Additional parameters that should be added
 * @param   array   $without     Array with parameters that should be removed
 * @return  string  $query       The url encoded string to be used in a HREF attribute
 */
function buildQuery($additional=array(), $without=array()) {
	$query  = $_SERVER["PHP_SELF"];
	$params = array_merge($_REQUEST, $additional);
	$without = array_merge($without, array("PHPSESSID"));

	// Werte entfernen
	foreach ($without AS $remove) {
		if (isset($params[$remove])) {
			unset($params[$remove]);
		}
	}

	$count = 0;
	foreach ($params AS $id => $val) {
		#if ($id == "PHPSESSID")  continue;		// Diese nicht übergeben
		$sign = ($count == 0) ? "?" : "&amp;";
		$query .= $sign.urlencode($id)."=".urlencode($val);
		$count++;
	}
	return $query;
}


/**
 * Output a <pre> formatted string with the name of the variable in front of it
 * @param $label  string  The name of the variable to output
 * @param $val    string  (private) The actual content of the variable
 */
function pre($label, $val="__undefin_e_d__") {
	if($val == "__undefin_e_d__") {

		/* The first argument is not the label but the
		variable to inspect itself, so we need a label.
		Let's try to find out it's name by peeking at
		the source code.
		*/

		/* The reason for using an exotic string like
		"__undefin_e_d__" instead of NULL here is that
		inspected variables can also be NULL and I want
		to inspect them anyway.
		*/

		$val  = $label;
		$bt   = debug_backtrace();
		$src  = file($bt[0]["file"]);
		$line = $src[ $bt[0]['line'] - 1 ];

		// let's match the function call and the last closing bracket
		preg_match( "#pre\((.+)\)#", $line, $match );

		/* let's count brackets to see how many of them actually belongs
		to the var name
		Eg:   die(inspect($this->getUser()->hasCredential("delete")));
		      We want:   $this->getUser()->hasCredential("delete")
		*/
		$max = @strlen($match[1]);
		$varname = "";
		$c = 0;
		for ($i = 0; $i < $max; $i++) {
			if (    $match[1][$i] == "(") $c++;
			elseif ($match[1][$i] == ")") $c--;
			if($c < 0) break;
			$varname .=  $match[1][$i];
		}
		$label = $varname;
	}

	// $label now holds the name of the passed variable ($ included)
	// Eg:   inspect($hello)
	//             => $label = "$hello"
	// or the whole expression evaluated
	// Eg:   inspect($this->getUser()->hasCredential("delete"))
	//             => $label = "$this->getUser()->hasCredential(\"delete\")"

	// now the actual function call to the inspector method,
	// passing the var name as the label:

	// return dInspect::dump($label, $val);
	// UPDATE: I commented this line because people got confused about
	// the dInspect class, wich has nothing to do with the issue here.

	#return array($val, $label);

	echo "<pre>";
	echo "--------- ".$label." ---------\n";
	print_r($val);
	echo "</pre>";
}
?>
