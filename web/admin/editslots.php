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

// AJAX: save selected services for a reservation
if (isset($P['ajax_res_services']) && $P['ajax_res_services'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    $rid = isset($P['rid']) ? (int)$P['rid'] : 0;
    // Accept sids as array (sids[]) or comma-separated fallback
    $sids = array();
    if (isset($P['sids'])) {
        if (is_array($P['sids'])) { $sids = $P['sids']; }
        elseif (is_string($P['sids'])) { $sids = array_filter(explode(',', $P['sids'])); }
    }
    $sids = array_values(array_unique(array_map('intval', $sids)));
    $ok=false; $msg='';
    try {
        if ($rid>0) {
            // ensure table
            $tbl = $DB->PreparedSelect("SHOW TABLES LIKE 'reservation_service_prices'", array(), false, false);
            if (!(is_array($tbl) && count($tbl) > 0)) { echo json_encode(array('ok'=>false,'message'=>'table missing')); exit; }

            // find client for this reservation
            $row = $DB->PreparedSelect(
                "SELECT c.id AS cid FROM reservations r JOIN times t ON r.time_id=t.id JOIN dates d ON t.date_id=d.id JOIN clients c ON d.client_id=c.id WHERE r.id=:rid",
                array('rid'=>$rid), false, false
            );
            $cid = (is_array($row) && isset($row[0]['cid'])) ? (int)$row[0]['cid'] : 0;

            // load client prices
            $clientPrices = array();
            if ($cid>0) {
                $rows = $DB->PreparedSelect("SELECT service_id, price_amount FROM client_service_prices WHERE client_id=:cid", array('cid'=>$cid), false, false);
                if (is_array($rows)) { foreach ($rows as $r) { $clientPrices[(int)$r['service_id']] = (float)$r['price_amount']; } }
            }
            // load fee_mid for involved services
            $feeMid = array();
            if (count($sids)>0) {
                $ph = array(); $pa = array(); foreach ($sids as $i=>$sid){ $ph[]=':s'.$i; $pa['s'.$i]=$sid; }
                $rows = $DB->PreparedSelect('SELECT id, fee_mid FROM gebueh_services WHERE id IN ('.implode(',', $ph).')', $pa, false, false);
                if (is_array($rows)) { foreach ($rows as $r) { $feeMid[(int)$r['id']] = (float)$r['fee_mid']; } }
            }

            // fetch existing RSP rows for this reservation
            $existing = array();
            $rows = $DB->PreparedSelect('SELECT service_id, price_amount FROM reservation_service_prices WHERE reservation_id = :rid', array('rid'=>$rid), false, false);
            if (is_array($rows)) { foreach ($rows as $r) { $existing[(int)$r['service_id']] = (float)$r['price_amount']; } }

            // upsert selected
            $sqlIns = "INSERT INTO reservation_service_prices (reservation_id, service_id, price_amount) VALUES (:rid, :sid, :amount) ON DUPLICATE KEY UPDATE price_amount = price_amount";
            $stmt = $DB->PrepareStatement($sqlIns);
            foreach ($sids as $sid) {
                $sid = (int)$sid; if ($sid<=0) continue;
                if (isset($existing[$sid])) {
                    // keep existing price
                    $DB->PreparedStatement($stmt, array('rid'=>$rid,'sid'=>$sid,'amount'=>$existing[$sid]), false, false);
                } else {
                    $amount = isset($clientPrices[$sid]) ? (float)$clientPrices[$sid] : (isset($feeMid[$sid]) ? (float)$feeMid[$sid] : 0.0);
                    $DB->PreparedStatement($stmt, array('rid'=>$rid,'sid'=>$sid,'amount'=>$amount), false, false);
                }
            }

            // delete unselected
            if (count($sids) > 0) {
                $ph = array(); $pa = array('rid'=>$rid); foreach ($sids as $i=>$sid){ $ph[]=':s'.$i; $pa['s'.$i]=$sid; }
                $sqlDel = 'DELETE FROM reservation_service_prices WHERE reservation_id = :rid AND service_id NOT IN ('.implode(',', $ph).')';
                $DB->PreparedStatement($sqlDel, $pa, false, false);
            } else {
                $DB->PreparedStatement('DELETE FROM reservation_service_prices WHERE reservation_id = :rid', array('rid'=>$rid), false, false);
            }

            $ok=true;
        } else { $msg='invalid rid'; }
    } catch (Exception $e) { $msg='error'; }
    echo json_encode(array('ok'=>$ok,'message'=>$msg));
    exit;
}

// AJAX: bulk save for one reservation
if (isset($P['ajax_res_price_bulk']) && $P['ajax_res_price_bulk'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    $rid = isset($P['rid']) ? (int)$P['rid'] : 0;
    $sids = isset($P['sid']) ? (array)$P['sid'] : array();
    $vals = isset($P['amount']) ? (array)$P['amount'] : array();
    $ok  = false; $msg='';
    try {
        if ($rid>0 && count($sids)===count($vals)) {
            $tbl = $DB->PreparedSelect("SHOW TABLES LIKE 'reservation_service_prices'", array(), false, false);
            if (is_array($tbl) && count($tbl) > 0) {
                $sql = "INSERT INTO reservation_service_prices (reservation_id, service_id, price_amount) \n"
                     . "VALUES (:rid, :sid, :amount) \n"
                     . "ON DUPLICATE KEY UPDATE price_amount = VALUES(price_amount)";
                $stmt = $DB->PrepareStatement($sql);
                for ($i=0; $i<count($sids); $i++) {
                    $sid = (int)$sids[$i]; if ($sid<=0) continue;
                    $val = $vals[$i]; if (is_string($val)) { $val = str_replace(',', '.', $val); }
                    $amount = (float)$val; if ($amount < 0) { $amount = 0.0; }
                    $DB->PreparedStatement($stmt, array('rid'=>$rid,'sid'=>$sid,'amount'=>$amount), false, false);
                }
                $ok = true;
            } else { $msg='Table missing'; }
        } else { $msg='Invalid params'; }
    } catch (Exception $e) { $msg='Error'; }
    echo json_encode(array('ok'=>$ok,'message'=>$msg));
    exit;
}

// Reservierungs-spezifische Servicepreise des Tages laden (wird nach $slots-Befüllung erneut gesetzt)
$reservation_service_prices = array();

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

// Jetzt, da $slots befüllt ist: Reservierungs-spezifische Preise laden
try {
    $resIds = array();
    foreach ($slots as $s) { if (isset($s['res_id']) && (int)$s['res_id'] > 0) { $resIds[] = (int)$s['res_id']; } }
    $resIds = array_values(array_unique($resIds));
    if (count($resIds) > 0) {
        $placeholders = array(); $params = array();
        foreach ($resIds as $i => $rid) { $placeholders[] = ':r'.$i; $params['r'.$i] = $rid; }
        $sql = 'SELECT reservation_id, service_id, price_amount FROM reservation_service_prices WHERE reservation_id IN ('.implode(',', $placeholders).')';
        $rows = $DB->PreparedSelect($sql, $params, false, false);
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $rid = (int)$r['reservation_id'];
                $sid = (int)$r['service_id'];
                $reservation_service_prices[$rid][$sid] = (float)$r['price_amount'];
            }
        }
    }
} catch (Exception $e) {}

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