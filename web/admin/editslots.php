<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editslots.php
 * Show/Add/Edit reservation slots for a time vaue
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";	// Check if admin logged in

$R = $_REQUEST;
$P = $_POST;

$date_id    = (isset($R["id"]))         ? (int)$R["id"]     : 0;
$durations  = (isset($R["durations"]))  ? $R["durations"]  : array("");
$startTimes = (isset($R["starttimes"])) ? $R["starttimes"] : array("");
$endTimes   = (isset($R["endtimes"]))   ? $R["endtimes"]   : array("");


// Get the previous page
$previous  = $breadcrumbs[PAGE];
$prevId    = getClientId($date_id);
$previous .= "?cid=".$prevId;

// Does this entry exist?
// Is this user allowed to edit this entry?
$allowed = checkIfAllowed($_SESSION["userid"], $date_id);

if ($allowed !== true) {
	header("Location: index.php");
}


// Generate a new day entry
if (isset($P["generate"])) {
	$success = generateTimeValues($date_id, $durations, $startTimes, $endTimes);
}


$infos = getDayInfos($date_id);
$slots = array();
$slots = $infos;


// Include the template
$infoText = "Reservierungen am ".$infos[0]["date"]." (".$infos[0]["client_name"].")";
require ROOT."/tpl/_include.php";
?> 