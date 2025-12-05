<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editclient.php
 * Edit/add a client
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";	// Check if admin logged in
require ROOT."/inc/email_text.php";

// Get the previous page
$previous  = $breadcrumbs[PAGE];


$R                  = $_REQUEST;
$clientID           = (isset($R["cid"]))                ? (int) $R["cid"]                     : 0;
$cName              = (isset($R["cName"]))              ? trim($R["cName"])                   : "";
$cEnabled           = (isset($R["cEnabled"]))           ? $R["cEnabled"]                      : "1";
$cText              = (isset($R["cText"]))              ? trim(stripslashes($R["cText"]))     : "";
$emailText          = (isset($R["emailText"]))          ? trim(stripslashes($R["emailText"])) : $email_default_text;
$contact_client_id  = (isset($R["contact_client_id"]))  ? (int) $R["contact_client_id"]       : 0;
$contact_masseur_id = (isset($R["contact_masseur_id"])) ? (int) $R["contact_masseur_id"]      : 0;
$cDays              = (isset($R["cDays[]"]))            ? $R["cDays[]"]                       : array(array("date" => "", "id" => ""));
$group_id           = (isset($R["group_id"]))           ? (int) $R["group_id"]                : 0;
$price							= (isset($R["price"]))              ? trim($R["price"])     : "";
$default_diagnosis  = isset($R['default_diagnosis']) ? trim($R['default_diagnosis']) : '';
$default_service_ids_req = isset($R['default_service_ids']) && is_array($R['default_service_ids']) ? array_map('intval', $R['default_service_ids']) : array();
$hasImage           = false;


// Get the group list if super admin
if ($SUPERADMIN === true) {
	require ROOT."/controller/admin-overview_groups-controller.php";
	$groupList = getAllGroups();
}


// Get the user list
require ROOT."/controller/admin-overview_users-controller.php";
$userList = getAllUsers();
// Services-Liste für Mehrfachauswahl
require ROOT."/controller/admin-overview_services-controller.php";
$services = getAllGebuehServices();

// Client-spezifische Servicepreise laden (Anzeige, kein Speichern in diesem Schritt)
$client_service_prices = array();
// Flags: Patientenrechnung notwendig & Masseurnamen anzeigen (defaults)
$patient_billing_required = 0;
$patient_invoice_flag    = 0;
// Prüfen, ob Spalten existieren
$__hasPatientBillingCol = false;
$__hasPatientInvoiceCol = false;
try {
	$col = $DB->PreparedSelect("SHOW COLUMNS FROM `clients` LIKE 'patient_billing_required'", array(), false, false);
	$__hasPatientBillingCol = (is_array($col) && count($col) > 0);
} catch (Exception $e) { $__hasPatientBillingCol = false; }
try {
	$col2 = $DB->PreparedSelect("SHOW COLUMNS FROM `clients` LIKE 'patient_invoice_flag'", array(), false, false);
	$__hasPatientInvoiceCol = (is_array($col2) && count($col2) > 0);
} catch (Exception $e) { $__hasPatientInvoiceCol = false; }
if ($clientID > 0) {
    try {
        $sql = "SELECT service_id, price_amount FROM client_service_prices WHERE client_id = :cid";
        $rows = $DB->PreparedSelect($sql, array('cid' => $clientID), false, false);
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $sid = (int)$r['service_id'];
                $client_service_prices[$sid] = (float)$r['price_amount'];
            }
        }
    } catch (Exception $e) {
        $client_service_prices = array();
    }
    // Patientenrechnung-/Masseurnamen-Flags laden
	if ($__hasPatientBillingCol || $__hasPatientInvoiceCol) {
		try {
			$row = $DB->PreparedSelect(
				"SELECT patient_billing_required, patient_invoice_flag FROM clients WHERE id = :cid",
				array('cid' => $clientID),
				false,
				false
			);
			if (is_array($row) && isset($row[0])) {
				if (isset($row[0]['patient_billing_required'])) {
					$patient_billing_required = (int)$row[0]['patient_billing_required'];
				}
				if (isset($row[0]['patient_invoice_flag'])) {
					$patient_invoice_flag = (int)$row[0]['patient_invoice_flag'];
				}
			}
		} catch (Exception $e) {}
	}
}


// Edit a client
if ($clientID > 0) {
	$client   = getClientInfos($clientID);
	#pre($client);
	#exit;

	if ($client !== false) {
		$cName              = trim($client["name"]);
		$cEnabled           = $client["enabled"];
		$cText              = htmlspecialchars(trim(stripslashes($client["greeting_text"])));
		$emailText          = (isset($client["email_text"][5])) ? htmlspecialchars(trim(stripslashes($client["email_text"]))) : $email_default_text;
		$contact_client_id  = (int) ($client["contact_client_id"]);
		$contact_masseur_id = (int) ($client["contact_masseur_id"]);
		$cDays              = $client["dates"];
		$group_id           = (int) $client["group_id"];
		$price				= $client["price"];
		$hash               = generateHashLink($client["hashlink"]);	// Use existing hash link
		$hasImage           = ( isset($client["image"][10]) );
		$associated_users   = $client["contacts"]["mixed"];
        // Defaults laden
        $default_diagnosis = isset($client['default_diagnosis']) ? (string)$client['default_diagnosis'] : '';
        $default_service_ids_loaded = isset($client['default_service_ids']) ? trim((string)$client['default_service_ids']) : '';
        $default_service_ids = array();
        if ($default_service_ids_req && count($default_service_ids_req)>0) {
            $default_service_ids = $default_service_ids_req;
        } elseif ($default_service_ids_loaded !== '') {
            $default_service_ids = array_values(array_filter(array_map('intval', explode(',', $default_service_ids_loaded)), function($v){ return $v>0; }));
        }

		// Buchungs-/Stornofrist korrekt setzen
		$booking_deadline_hours = isset($client["booking_deadline_hours"]) ? $client["booking_deadline_hours"] : 0;
		// Doppelbuchungsmodus laden
		$avoid_double_bookings_mode = isset($client["avoid_double_bookings_mode"]) ? $client["avoid_double_bookings_mode"] : 'none';

		// All users that are directly associated with this client
		$user_ids           = $client["contacts"]["direct"];
	}


	// If not super admin, check if allowed to edit this client
	if ($SUPERADMIN !== true) {
		if (isset($_SESSION["group_ids"][$group_id]) || isset($associated_users[$_SESSION["userid"]])) {

		}
		else {
			header("Location: ".$previous);
			exit;
		}
	}
}

// Add a new client
else {
	// Generate a new hash link
	$hash  = generateHashLink();
	// Defaults für neue Kunden
	$avoid_double_bookings_mode = 'none';
    // Default-Felder initialisieren
    $default_service_ids      = $default_service_ids_req;
    $patient_billing_required = 0;
    $patient_invoice_flag     = 0;
}


// Include the template
require ROOT."/tpl/_include.php";
