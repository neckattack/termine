<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * export.php
 * Export a client to a CSV file
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";	// Check if admin logged in
require ROOT."/inc/export.php";		// Include export functions


$R = $_REQUEST;

// Get the data
$array = getExportData($R["cid"]);
$formattedArray = formatExportData($array);

// Export the file
// exportAsCSV($array, $headlines, $delimiter)
exportAsCSV($formattedArray, false, ";")

?>