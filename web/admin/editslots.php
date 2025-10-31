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

// AJAX: single price save
if (isset($P['ajax_res_price']) && $P['ajax_res_price'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    $rid = isset($P['rid']) ? (int)$P['rid'] : 0;
    $sid = isset($P['sid']) ? (int)$P['sid'] : 0;
    $val = isset($P['amount']) ? $P['amount'] : null;
    $ok  = false; $msg = '';
    try {
        if ($rid>0 && $sid>0 && $val!==null) {
            if (is_string($val)) { $val = str_replace(',', '.', $val); }
            $amount = (float)$val; if ($amount < 0) { $amount = 0.0; }
            $tbl = $DB->PreparedSelect("SHOW TABLES LIKE 'reservation_service_prices'", array(), false, false);
            if (is_array($tbl) && count($tbl) > 0) {
                $sql = "INSERT INTO reservation_service_prices (reservation_id, service_id, price_amount) \n"
                     . "VALUES (:rid, :sid, :amount) \n"
                     . "ON DUPLICATE KEY UPDATE price_amount = VALUES(price_amount)";
                $DB->PreparedStatement($sql, array('rid'=>$rid,'sid'=>$sid,'amount'=>$amount), false, false);
                $ok = true;
            } else {
                $msg = 'Table reservation_service_prices missing';
            }
        } else {
            $msg = 'Invalid parameters';
        }
    } catch (Exception $e) { $msg = 'Error'; }
    echo json_encode(array('ok'=>$ok, 'message'=>$msg));
    exit;
}

// Save reservation prices (step 2)
if (isset($P['save_res_prices']) && isset($P['res_prices']) && is_array($P['res_prices'])) {
    try {
        // Check table exists
        $tbl = $DB->PreparedSelect("SHOW TABLES LIKE 'reservation_service_prices'", array(), false, false);
        $hasTable = (is_array($tbl) && count($tbl) > 0);
        if ($hasTable) {
            $sql = "INSERT INTO reservation_service_prices (reservation_id, service_id, price_amount) \n"
                 . "VALUES (:rid, :sid, :amount) \n"
                 . "ON DUPLICATE KEY UPDATE price_amount = VALUES(price_amount)";
            $stmt = $DB->PrepareStatement($sql);
            foreach ($P['res_prices'] as $rid => $row) {
                $rid = (int)$rid; if ($rid <= 0 || !is_array($row)) continue;
                foreach ($row as $sid => $val) {
                    $sid = (int)$sid; if ($sid <= 0) continue;
                    if (is_string($val)) { $val = str_replace(',', '.', $val); }
                    $amount = (float)$val; if ($amount < 0) { $amount = 0.0; }
                    $DB->PreparedStatement($stmt, array('rid'=>$rid, 'sid'=>$sid, 'amount'=>$amount), false, false);
                }
            }
        }
    } catch (Exception $e) {
        // no-op: fail silently for now
    }
    // Redirect back to avoid resubmission
    header('Location: '.ABSURL.'web/admin/editslots.php?id='.$date_id);
    exit;
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