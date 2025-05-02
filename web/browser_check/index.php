<?php
include "get_browser.php";

$browser = browser_detection("full_assoc");
$browser["user_agent"] = $_SERVER['HTTP_USER_AGENT'];

$server = array();
$server_fields = array_flip(array(
	"CLIENT_IP",
	"FORWARDED",
	"FORWARDED_FOR",
	"FORWARDED_FOR_IP",
	"GATEWAY_INTERFACE",
	"HTTP_ACCEPT",
	"HTTP_ACCEPT_ENCODING",
	"HTTP_ACCEPT_LANGUAGE",
	"HTTP_CACHE_CONTROL",
	"HTTP_CLIENT_IP",
	"HTTP_CONNECTION",
	"HTTP_COOKIE",
	"HTTP_FORWARDED",
	"HTTP_FORWARDED_FOR",
	"HTTP_FORWARDED_FOR_IP",
	"HTTP_HOST",
	"HTTP_PROXY_CONNECTION",
	"HTTP_USER_AGENT",
	"HTTP_VIA",
	"HTTP_X_FORWARDED",
	"HTTP_X_FORWARDED_FOR",
	"PHP_SELF",
	"QUERY_STRING",
	"REMOTE_ADDR",
	"REMOTE_PORT",
	"REQUEST_METHOD",
	"REQUEST_SCHEME",
	"SERVER_ADDR",
	"SERVER_NAME",
	"SERVER_PORT",
	"SERVER_PROTOCOL",
	"VIA",
	"X_FORWARDED",
	"X_FORWARDED_FOR",
));

foreach ($_SERVER AS $key => $val) {
	if (isset($server_fields[$key])) {
		$server[$key] = $val;
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>Browser Check</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<style type="text/css">
	body {
		font-family: Helvetica, Verdana, sans-serif;
		font-size: 14px;
	}

	noscript p {
		display: inline-block;
		font-weight: bold;
		background-color: #FFFF00;
	}

	#js_on {
		background-color: #00FF00;
	}

	textarea {
		display: block;
	}


	</style>


	<script type="text/javascript">
	/* <![CDATA[ */
	String.prototype.lpad = function(padString, length) {
		var str = this;
		for (var x=0; x<length; x++) {
			str = padString + str;
		}
		return str;
	};

	function print_r2(theObj, level) {
		var txt, padding;
		level   = (typeof(level) === "undefined") ? 0 : level;
		txt     = "";
		padding = level * 4;

		if (theObj.constructor == Array || theObj.constructor == Object) {
			
			for (var p in theObj) {
				if (theObj[p]) {
					if (theObj[p].constructor == Array || theObj[p].constructor == Object) {
						txt += ("["+p+"] => "+typeof(theObj)+"\n").lpad(" ", padding);
						txt += print_r2(theObj[p], (level+1));
					}
					else {
						txt += ("["+p+"] => "+theObj[p]+"\n").lpad(" ", padding);
					}
				}
			}
		}
		return txt;
	}
	/* ]]> */
	</script>
</head>
<body>

<div>
<h1>PHP Test</h1>
<textarea cols="100" rows="20" id="phpTest">
*******************
***** Browser *****
*******************
<?php var_dump($browser); ?>


******************
***** Server *****
******************
<?php print_r($server); ?>
</textarea>


<h1>JavaScript Test</h1>
<p>JavaScript enabled: <span id="js_on"><span style="font-weight:bold; background-color: #FF0000;">No</span></span></p>
<noscript><p>If you see this line, JavaScript is disabled.</p></noscript>
<textarea cols="100" rows="20" id="jsTest"></textarea>


<script type="text/javascript">
	/* <![CDATA[ */
	document.getElementById("js_on").innerHTML = "Yes";

	var nav = print_r2(navigator);
	document.getElementById("jsTest").value = nav;
	/* ]]> */
</script>
</div>
</body>
</html>