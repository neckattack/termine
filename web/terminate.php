<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * index.php
 * The landing page for the customer
 */

error_reporting(-1);				// Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";				// Defines the ROOT constant
require ROOT."/inc/_include.php";
include ROOT."/controller/index-controller.php";
include ROOT."/controller/admin-editslots-controller.php";

$emailHash = @$_GET["e"];
$times = @$_GET["t"];

$reservations = [];

if ( $emailHash && $times ) {

	$toolate    = false;
	$error    = false;
	$success    = false;

	$times = explode("I", $times);
	$times = array_unique($times);

	// Access the database
	$DB = Database::getInstance();	// Create the database object. See sql.pdo.php for the Database class
	$DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);	// Connect to the database

	$reservations = getReservationsForEmailHash($emailHash, $times);
	
	if ($reservations) {
		$reservations_times = array_map(function($r){return $r['time_id'];}, $reservations);

		$client = getClientInfos($reservations[0]["client_id"]);

		$result = getDates($client["id"], false, true); // array of ["dates", "times", "times_assoc"]
        $date_id  = (isset($result["dates"][0])) ? (int) $result["dates"][0]["id"] : 0;

        // Tag: stornovorlauf – Standardauswahl: Nächstes mögliches Datum mit buchbarem Slot
        $deadline = isset($client['booking_deadline_hours']) ? (int)$client['booking_deadline_hours'] : 0;
        $nowTs = time();
        if (!empty($result["dates"])) {
            foreach ($result["dates"] as $d) {
                $did = (int)$d['id'];
                if (isset($result['times_assoc'][$did])) {
                    foreach ($result['times_assoc'][$did] as $t) {
                        if (!isset($t['taken'])) { // frei
                            // $t['date'] ist Y-m-d aus getTimesForDate, $t['time_start'] ist HH:MM
                            $slotTs = strtotime($t['date'].' '.$t['time_start']);
                            $buchbar = ($deadline === 0 || ($slotTs - $nowTs) > $deadline*3600);
                            if ($buchbar) { $date_id = $did; break 2; }
                        }
                    }
                }
            }
        }
		
		//getting data for selected date time to update
		$selected_reservation = getSelectedReservation($_GET['t']);

	}
}


// if it POST request (update) and it have all needed fields
if ( $reservations && !empty($_POST) && isset($_POST['h']) && isset($_POST['id']) && $client["hashlink"] == $_POST['h'] && $client["id"] == $_POST['id'] ) {
	// $times - old array
	$new_times = (isset($_POST['times'])) ? $_POST['times'] : [];

	$times_to_add = array_merge([],array_diff($new_times,$times));
	$times_to_delete = array_merge([],array_diff($times,$new_times));

	if ( empty($times_to_add) && empty($times_to_delete) ){ // nothing changed
		require ROOT."/tpl/_include.php";
		exit();
	}

	$name = $reservations[0]["name"];
	$email = $reservations[0]["email"];

	$checkAvail = !(empty($times_to_add)) ? checkTimesAvailable($times_to_add)["allAvailable"] : true;

    if ($checkAvail === true) {
        $loc_success = !(empty($times_to_add)) ? setReservations($times_to_add, $name, $email) : true;
        if ( $loc_success ){ // if new created
        	if ( $times_to_delete ){
        		$reservations_to_delete = array_filter($reservations, function($value) use ($times_to_delete) {
				    return in_array($value["time_id"], $times_to_delete);
				});
	        	foreach ($reservations_to_delete as $value) {
					$loc_success = $loc_success && deleteReservation($value["id"]);
	        	}
        	}
        }
        if ( $loc_success ){ // if new created and old deleted
            $message  = $client["email_text"];
            $contact  = getContactInfo($client["id"]);

            $email = sendTerminateMail($email, $name, $new_times, $times_to_add, $times_to_delete, $contact);
            $success = true;
        } else {
        	$error = true;
        }
    } else {
        $toolate = true;
    }

    if ( $success ) {
        // Tag: stornovorlauf – Nach erfolgreichem Verschieben zur Übersicht aller Buchungen springen (mit Erfolgsflag)
        header("Location: /web/bookings.php?e=".$emailHash."&moved=1");
        exit();
    }
}

// Include the template files
require ROOT."/tpl/_include.php";