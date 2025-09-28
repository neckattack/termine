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
				// Ermittele betroffene Daten (YYYY-mm-dd) der gewählten time-IDs inkl. Mapping t.id -> d.date
				$idStr = implode("', '", array_map('intval', $times));
				$rows = $DB->PreparedSelect(
					"SELECT t.id AS time_id, d.date FROM times t JOIN dates d ON t.date_id = d.id WHERE t.id IN ('".$idStr."') AND d.client_id = :cid",
					array('cid'=>$clientID), false, false
				);
				$dates = array();
				$timesByDate = array();
				if (is_array($rows)) {
					foreach ($rows as $r) {
						$dates[] = $r['date'];
						$timesByDate[$r['date']] = isset($timesByDate[$r['date']]) ? $timesByDate[$r['date']] : array();
						$timesByDate[$r['date']][] = (int)$r['time_id'];
					}
				}

				// 1) In-Request-Validierung: Doppelte Auswahl gemäß Modus verhindern
				if ($mode === 'per_client' && count($times) > 1) {
					$output["success"] = 0;
					$output["error"] = "duplicate_booking_in_request";
					$output["mode"] = $mode;
					$output["errors"] = 'Sie haben mehrere Termine ausgewählt. In dieser Massagereihe ist nur eine Buchung pro Person erlaubt.';
					break;
				}
				if ($mode === 'per_date') {
					foreach ($timesByDate as $d => $tids) {
						if (count($tids) > 1) {
							$output["success"] = 0;
							$output["error"] = "duplicate_booking_in_request";
							$output["mode"] = $mode;
							$output["errors"] = 'Sie haben an einem Termin mehrere Zeiten ausgewählt. Pro Termin ist nur eine Buchung pro Person erlaubt.';
							break 2;
						}
					}
				}

				$dupFound = false;
				if ($mode === 'per_date' && count($dates) > 0) {
					$datesStr = "'".implode("','", $dates)."'";
					$sql = "SELECT 1 FROM reservations r JOIN times t ON r.time_id=t.id JOIN dates d ON t.date_id=d.id \n"
						."WHERE d.client_id = :cid AND d.date IN (".$datesStr.") AND d.date >= CURDATE() AND LOWER(r.email) = LOWER(:email) LIMIT 1";
					$dup = $DB->PreparedSelect($sql, array('cid'=>$clientID, 'email'=>$email), false, false);
					$dupFound = is_array($dup) && count($dup) > 0;
				} elseif ($mode === 'per_client') {
					$sql = "SELECT 1 FROM reservations r JOIN times t ON r.time_id=t.id JOIN dates d ON t.date_id=d.id \n"
						."WHERE d.client_id = :cid AND d.date >= CURDATE() AND LOWER(r.email) = LOWER(:email) LIMIT 1";
					$dup = $DB->PreparedSelect($sql, array('cid'=>$clientID, 'email'=>$email), false, false);
					$dupFound = is_array($dup) && count($dup) > 0;
				}
				if ($dupFound) {
					$output["success"] = 0;
					$output["error"] = "duplicate_booking";
					$output["mode"] = $mode;
					$output["errors"] = ($mode === 'per_date')
						? 'Dies ist eine zweite Buchung an diesem Termin. Leider ist dies nicht erlaubt.'
						: 'Dies ist eine zweite Buchung in dieser Massagereihe. Leider ist dies nicht erlaubt.';
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