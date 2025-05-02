<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Include the controller PHP script for this page
 * Requires the ROOT and PAGE constant
 */
if (!isset($PAGE)) {
	die("Page not found.");
}
include ROOT."/controller/_general-controller.php";

// Include the controller only if it exists
is_file(ROOT."/controller/".PAGE."-controller.php") AND include ROOT."/controller/".PAGE."-controller.php";
?>