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
if (is_array($infos)) {
    $slots = $infos;
}

// Patientenrechnung-Flag und Client-Daten laden
$patient_billing_required = 0;
$client_id_for_day = 0;
$client_service_prices = array();
try {
    $row = $DB->PreparedSelect(
        "SELECT c.id AS client_id, c.patient_billing_required FROM dates d JOIN clients c ON c.id = d.client_id WHERE d.id = :did",
        array('did' => $date_id),
        false,
        false
    );
    if (is_array($row) && isset($row[0]['patient_billing_required'])) {
        $patient_billing_required = (int)$row[0]['patient_billing_required'];
        $client_id_for_day = isset($row[0]['client_id']) ? (int)$row[0]['client_id'] : 0;
    }
} catch (Exception $e) {}

// Client-spezifische Servicepreise laden (für Preis-Box UI)
if ($client_id_for_day > 0) {
    try {
        $rows = $DB->PreparedSelect(
            "SELECT service_id, price_amount FROM client_service_prices WHERE client_id = :cid",
            array('cid' => $client_id_for_day),
            false,
            false
        );
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $client_service_prices[(int)$r['service_id']] = (float)$r['price_amount'];
            }
        }
    } catch (Exception $e) {}
}


// Include the template
if (is_array($infos) && isset($infos[0]["date"]) && isset($infos[0]["client_name"])) {
    $infoText = "Reservierungen am ".$infos[0]["date"]." (".$infos[0]["client_name"].")";
} else {
    $infoText = "Reservierungen";
}
require ROOT."/tpl/_include.php";
?> 