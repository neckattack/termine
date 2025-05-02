<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */

// Absolute path on the server
if (!defined('ROOT')) {
	$docroot = dirname(realpath(__FILE__));
	$docroot = explode(DIRECTORY_SEPARATOR, $docroot);

	// Remove trailing slash
	if (empty($docroot[count($docroot)-1])) {
		array_pop($docroot);
	}

	// Remove last value of path, because root is one level above
	array_pop($docroot);
	$root = implode(DIRECTORY_SEPARATOR, $docroot);

	define('ROOT', $root);
}

// Relative web path from within the site
if (!defined('WEBDIR')) {
	$depthlink = str_replace(ROOT.DIRECTORY_SEPARATOR.'web', "", getcwd());
	$depth = substr_count($depthlink, DIRECTORY_SEPARATOR);
	$dirs  = "";
	for ($x=0; $x<$depth; $x++) {
		$dirs .= "../";
	}
	define('WEBDIR', $dirs);
}

// Relative web path from the web directory
// Used for the controller files (directories are added as "nameOfDirectory-")
if (!defined('DIRPATH')) {
	$dirpath = str_replace(ROOT.DIRECTORY_SEPARATOR.'web', "", getcwd());
	// Replace the directory separator if it's the first character
	$dirpath = (isset($dirpath[0]) && $dirpath[0] === DIRECTORY_SEPARATOR) ? substr($dirpath, 1) : $dirpath;


	// If last character is not a directory separator, add it
	$lastchar = strlen($dirpath)-1;
	$dirpath = (isset($dirpath[$lastchar]) && $dirpath[$lastchar] !== DIRECTORY_SEPARATOR) ? $dirpath.DIRECTORY_SEPARATOR : $dirpath;

	// Replace all directory separators with a hyphen
	$dirpath = str_replace(DIRECTORY_SEPARATOR, "-", $dirpath);

	// Remove all spaces
	$dirpath = str_replace(" ", "", $dirpath);


	define('DIRPATH', $dirpath);
}


// Absolute URL to the site
if (!defined('ABSURL')) {
	if (!$_SERVER["DOCUMENT_ROOT"]) {
		$_SERVER["DOCUMENT_ROOT"] = ROOT;
	}
	// Remove trailing slash from $_SERVER["DOCUMENT_ROOT"]
	$docroot = (substr($_SERVER["DOCUMENT_ROOT"], -1) == "/") ? substr($_SERVER["DOCUMENT_ROOT"], 0, -1) : $_SERVER["DOCUMENT_ROOT"];
	$http    = (isset($_SERVER['HTTPS']))       ? 'https://' : 'http://';
	$user    = (isset($_SERVER['REMOTE_USER'])) ? $_SERVER['REMOTE_USER'].'@' : '';
	$host    = (isset($_SERVER['HTTP_HOST']))   ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	$port    = ( (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] == 443) || $_SERVER['SERVER_PORT'] == 80) ? '' : ':'.$_SERVER['SERVER_PORT'];

	$url     = str_replace("\\", "/", ROOT);		// Replace any Windows backlash with a forward slash
	$url     = str_replace($docroot, "", $url);		// Remove the root directory from the document_root to find out the path relative from the document root
	$url     = $http.$user.$host.$port.$url."/";	// Take the HTTP_HOST and add the relative path to get the complete absolute path for the URL

	define('ABSURL', $http.$_SERVER['HTTP_HOST'].'/');
}
