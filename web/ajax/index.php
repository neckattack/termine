<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
error_reporting(-1);					// Enable PHP error reporting (set to 0 to disable)
$AJAX = true;
$PAGE = basename(__FILE__);
require "_root_.php";					// Defines the ROOT constant
require ROOT."/inc/_include.php";


$R      = $_REQUEST;
$output = array(
	"success" => 0
);


switch ($R["action"]) {
	
	// Get the time values for a specific date
	case "getTime":
		$dateID = (int) $R["id"];
		$times  = getTimesForDate($dateID);
		$output = array(
			"success" => 1,
			"data" =>$times
		);
	break;
	

	// Send data
	case "ajaxSend":
		$clientID = (int) $R["id"];
		$client   = getClientInfos($clientID);
		$times    = @$R["times"];
		$name     = @$R["name"];
		$email    = @strtolower(trim($R["email"]));
		$message  = $client["email_text"];
		
		
		// Register to times
		$checkAvail = checkTimesAvailable($times);
		
		// All times are free, prüfen auf Doppelbuchung gemäß Kundenmodus
		if ($checkAvail["allAvailable"] === true) {
			$mode = isset($client['avoid_double_bookings_mode']) ? $client['avoid_double_bookings_mode'] : 'none';
			if ($mode !== 'none' && is_array($times) && count($times) > 0) {
				// Ermittele betroffene Daten (YYYY-mm-dd) der gewählten time-IDs
				$idStr = implode("', '", array_map('intval', $times));
				$dateRows = $DB->PreparedSelect(
					"SELECT DISTINCT d.date FROM times t JOIN dates d ON t.date_id = d.id WHERE t.id IN ('".$idStr."') AND d.client_id = :cid",
					array('cid'=>$clientID), false, false
				);
				$dates = array(); if (is_array($dateRows)) { foreach ($dateRows as $r) { $dates[] = $r['date']; } }
				$dupFound = false;
				if ($mode === 'per_date' && count($dates) > 0) {
					$datesStr = "'".implode("','", $dates)."'";
					$sql = "SELECT 1 FROM reservations r JOIN times t ON r.time_id=t.id JOIN dates d ON t.date_id=d.id \n"
						."WHERE d.client_id = :cid AND d.date IN (".$datesStr.") AND md5(r.email) = md5(:email) LIMIT 1";
					$dup = $DB->PreparedSelect($sql, array('cid'=>$clientID, 'email'=>$email), false, false);
					$dupFound = is_array($dup) && count($dup) > 0;
				} elseif ($mode === 'per_client') {
					$sql = "SELECT 1 FROM reservations r JOIN times t ON r.time_id=t.id JOIN dates d ON t.date_id=d.id \n"
						."WHERE d.client_id = :cid AND md5(r.email) = md5(:email) LIMIT 1";
					$dup = $DB->PreparedSelect($sql, array('cid'=>$clientID, 'email'=>$email), false, false);
					$dupFound = is_array($dup) && count($dup) > 0;
				}
				if ($dupFound) {
					$output["success"] = 0;
					$output["error"] = "duplicate_booking";
					$output["mode"] = $mode;
					break;
				}
			}

			$success = setReservations($times, $name, $email);
			if ($success !== false) {
				$output["success"] = 1;
				$output["data"] = $success;

				// Get the contact information for this client
				$contact = getContactInfo($clientID);
				
				// Send confirmation e-mail
				$email = sendConfirmationMail($email, $name, $times, $contact, $message);
				$output["email"] = $email;
			}
		}
		
		// Some were taken, return these 
		else {
			$output["taken"] = $checkAvail["taken"];
		}
		
		
	break;
}


echo json_encode($output);

?>