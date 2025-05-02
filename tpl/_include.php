<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Include the template PHP script for this page
 * Requires the ROOT and PAGE constant
 */
if (!isset($PAGE)) {
	die("Page not found.");
}

// Include the template only if it exists
is_file(ROOT."/tpl/".PAGE."-tpl.php") AND include ROOT."/tpl/".PAGE."-tpl.php";
?>