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

$date_id  = 0;
$hashlink = (isset($_REQUEST["h"])) ? $_REQUEST["h"] : false;		// The hash link which identifies the client


// Access the database
$DB = Database::getInstance();	// Create the database object. See sql.pdo.php for the Database class
$DB->connect($config["server"], $config["database"], $config["user"], $config["pass"]);	// Connect to the database

// Get client infos and check if the hashlink exists
$sql    = "SELECT `id` FROM `clients` WHERE `hashlink` = :hashlink AND `enabled` = '1'";
$client = $DB->PreparedSelect($sql, array("hashlink" => $hashlink), false, false);


// No client found
// Display something. Welcome page?
if (!$client || $client[0]["id"] < 1) {
	// Redirect to welcome page
	#header("Location: welcome");
	header("Location: welcome.php");
}


// The client landing page (reservations)
else {
	// Include the landing page
	$client     = getClientInfos((int) $client[0]["id"]);
	$clientId   = (int) $client["id"];
	$date_id    = 0;
	$times      = false;
	$toolate    = false;
	$success    = false;
	$taken      = array();
	$reserved   = array();
	$messages   = array();
	$error      = 0;
	$hasImage   = false;
	$imageLink  = "";
	$contact_id = (int) $client["contact_masseur_id"];
	$message    = $client["email_text"];


	// No-JavaScript fallback
	if (isset($_POST) && count($_POST) > 0) {
		$P       = $_POST;
		$date_id = (isset($P["date"])) ? (int) $P["date"] : 0;

		// Submit button has been triggered
		if (isset($P["submit"])) {
			while ($error === 0) {
				// Time missing
				if (!isset($P["times"][0])) {
					$messages[] = "Bitte wählen Sie mindestens einen Termin aus";
					$error++;
				}

				// Name missing (Vorname und Nachname)
				if (empty($P["first_name"]) || trim($P["first_name"]) === "") {
					$messages[] = "Bitte geben Sie Ihren Vornamen an";
					$error++;
				}
				if (empty($P["last_name"]) || trim($P["last_name"]) === "") {
					$messages[] = "Bitte geben Sie Ihren Nachnamen an";
					$error++;
				}

				// Email missing
				if (!isset($P["email"][1]) || $P["email"] == "E-Mail") {
					$messages[] = "Bitte geben Sie eine gültige E-Mail-Adresse an";
					$error++;
				}

				// Wenn Patientenrechnung erforderlich: zusätzliche Pflichtfelder prüfen
				if (!empty($client['patient_billing_required'])) {
					$street    = isset($P['street']) ? trim($P['street']) : '';
					$house_no  = isset($P['house_no']) ? trim($P['house_no']) : '';
					$zip       = isset($P['zip']) ? trim($P['zip']) : '';
					$city      = isset($P['city']) ? trim($P['city']) : '';
					$birthdate = isset($P['birthdate']) ? trim($P['birthdate']) : '';
					if ($street === '' || $house_no === '' || $zip === '' || $city === '') {
						$messages[] = "Bitte geben Sie Ihre vollständige Rechnungsadresse an (Straße, Hausnummer, PLZ, Stadt)";
						$error++;
					}
					if ($birthdate === '' || !preg_match('#^\d{2}\.\d{2}\.\d{4}$#', $birthdate)) {
						$messages[] = "Bitte geben Sie Ihr Geburtsdatum im Format TT.MM.JJJJ an";
						$error++;
					}
				}

				if ($error > 0) {
					break;
				}


				// Insert the reservation
				$times = $P["times"];
				$first_name = trim($P["first_name"]);
				$last_name  = trim($P["last_name"]);
				$name  = $first_name . ' ' . $last_name; // Für E-Mail-Versand
				$email = $P["email"];
				$street    = isset($P['street']) ? trim($P['street']) : '';
				$house_no  = isset($P['house_no']) ? trim($P['house_no']) : '';
				$zip       = isset($P['zip']) ? trim($P['zip']) : '';
				$city      = isset($P['city']) ? trim($P['city']) : '';
				$birthdate = isset($P['birthdate']) ? trim($P['birthdate']) : '';

                // Tag: stornovorlauf – Backend-Fristprüfung (Buchungs-/Stornofrist)
                $deadline = isset($client["booking_deadline_hours"]) ? (int)$client["booking_deadline_hours"] : 0;
                if ($deadline > 0 && is_array($times) && count($times) > 0) {
                    $idStr = implode("', '", array_map('intval', $times));
                    $sql   = "SELECT `t`.`id`, `d`.`date`, `t`.`time_start` FROM `times` AS `t` LEFT JOIN `dates` AS `d` ON (`t`.`date_id` = `d`.`id`) WHERE `t`.`id` IN ('".$idStr."')";
                    $res   = $DB->PreparedSelect($sql, array(), false, false);
                    $tooSoon = array();
                    $nowTs = time();
                    foreach ($res as $row) {
                        // d.date ist Y-m-d, time_start HH:MM:SS
                        $slotTs = strtotime($row['date'].' '.$row['time_start']);
                        if (($slotTs - $nowTs) <= ($deadline * 3600)) {
                            $tooSoon[] = (int)$row['id'];
                        }
                    }
                    if (count($tooSoon) > 0) {
                        $toolate = true;
                        $taken   = $tooSoon; // Wiederverwendung der bestehenden Anzeige-Logik
                        break; // Abbrechen ohne Reservierung
                    }
                }

				// Register to times
				$checkAvail = checkTimesAvailable($times);

				// All times are free, set reservation
				if ($checkAvail["allAvailable"] === true) {
					$reserved = setReservations($times, $name, $email);

					if ($reserved !== false) {
						$success = true;

						// Get the contact information for this client
						$contact = getContactInfo($clientId);


						// Send confirmation e-mail
						// No HTML
						// $email = sendConfirmationMail($email, $name, $times, $contact, $message);

                        // Nach erfolgreicher Reservierung: Patient per E-Mail upserten
                        try {
                            $eml = strtolower(trim($P['email']));
                            if ($eml !== '') {
                                // Patient suchen
                                $prow = $DB->PreparedSelect('SELECT * FROM patients WHERE LOWER(email) = :em', array('em'=>$eml), false, false);
                                $pid = (is_array($prow) && isset($prow[0]['id'])) ? (int)$prow[0]['id'] : 0;
                                // Vor- und Nachname direkt verwenden
                                $first = $first_name;
                                $last = $last_name;
                                // Geburtsdatum in Y-m-d transformieren, wenn gültig
                                $bdSql = null; if (preg_match('#^(\d{2})\.(\d{2})\.(\d{4})$#', $birthdate, $m)) { $bdSql = $m[3].'-'.$m[2].'-'.$m[1]; }

                                if ($pid <= 0) {
                                    // Insert neuer Patient
                                    $sql = 'INSERT INTO patients (first_name, last_name, email, street, house_no, zip, city, birthdate, created_at) VALUES (:fn,:ln,:em,:st,:hn,:zp,:ct,:bd,NOW())';
                                    $DB->PreparedStatement($sql, array(
                                        'fn'=>$first, 'ln'=>$last, 'em'=>$eml,
                                        'st'=>$street, 'hn'=>$house_no, 'zp'=>$zip, 'ct'=>$city, 'bd'=>$bdSql
                                    ), false, false);
                                } else {
                                    // Nur leere Felder befüllen
                                    $row = $prow[0];
                                    $set = array(); $pa = array('em'=>$eml);
                                    if ($first !== '' && (empty($row['first_name']))) { $set[] = 'first_name = :fn'; $pa['fn'] = $first; }
                                    if ($last !== ''  && (empty($row['last_name'])))  { $set[] = 'last_name = :ln';  $pa['ln'] = $last; }
                                    if ($street !== '' && (empty($row['street'])))    { $set[] = 'street = :st';     $pa['st'] = $street; }
                                    if ($house_no !== '' && (empty($row['house_no']))) { $set[] = 'house_no = :hn';   $pa['hn'] = $house_no; }
                                    if ($zip !== '' && (empty($row['zip'])))          { $set[] = 'zip = :zp';        $pa['zp'] = $zip; }
                                    if ($city !== '' && (empty($row['city'])))        { $set[] = 'city = :ct';       $pa['ct'] = $city; }
                                    if ($bdSql !== null && (empty($row['birthdate']))) { $set[] = 'birthdate = :bd';  $pa['bd'] = $bdSql; }
                                    if (count($set) > 0) {
                                        $DB->PreparedStatement('UPDATE patients SET '.implode(', ',$set).' WHERE LOWER(email)=:em', $pa, false, false);
                                    }
                                }
                            }
                        } catch (Exception $e) { /* ignore */ }
                    }
				}

				// Some were taken, return these
				else {
					$toolate = true;
					$taken   = $checkAvail["taken"];
				}

				break;
			}
		}
	}


	// Get the result for this client
	$result = getDates($client["id"], false, true);	// set $alltimes to true so will return all times in "times_assoc" array

	// Get the date id of the first entry, only if date_id is 0
	if ($date_id === 0) {
		$date_id  = (isset($result["dates"][0])) ? (int) $result["dates"][0]["id"] : 0;

        // Tag: stornovorlauf – Standardauswahl auf nächstmögliches Datum mit buchbarem Slot setzen
        $deadline = isset($client['booking_deadline_hours']) ? (int)$client['booking_deadline_hours'] : 0;
        $nowTs = time();
        if (!empty($result["dates"])) {
            foreach ($result["dates"] as $d) {
                $did = (int)$d['id'];
                if (isset($result['times_assoc'][$did])) {
                    foreach ($result['times_assoc'][$did] as $t) {
                        if (!isset($t['taken'])) { // frei
                            $slotTs = strtotime($t['date'].' '.$t['time_start']);
                            $buchbar = ($deadline === 0 || ($slotTs - $nowTs) > $deadline*3600);
                            if ($buchbar) { $date_id = $did; break 2; }
                        }
                    }
                }
            }
        }
    }


	// Get the image
	$sql  = "SELECT ";
	$sql .= "IF(`c`.`image` IS NULL OR `c`.`image` = '', 0, 1) AS `client_has_image`, \n";
	$sql .= "IF(`g`.`image` IS NULL OR `g`.`image` = '', 0, 1) AS `group_has_image` \n";
	$sql .= "FROM `clients` AS `c` \n";
	$sql .= "LEFT JOIN `groups` AS `g` ON (`g`.`group_id` = `c`.`group_id`) \n";
	$sql .= "WHERE `c`.`id` = :id";
	$params = array(
		"id" => $clientId
	);
	$res = $DB->PreparedSelect($sql, $params, false, false);

	if (isset($res[0]["client_has_image"]) && $res[0]["client_has_image"] == 1) {
		$type     = "client";
		$owner_id = $clientId;
		$hasImage = true;

	}
	elseif (isset($res[0]["group_has_image"]) && $res[0]["group_has_image"] == 1) {
		$type     = "group";
		$owner_id = (int) $client["group_id"];
		$hasImage = true;
	}

	if ($hasImage === true) {
		$imageLink = "id=".$owner_id."&type=".$type;
		$imageLink = "s=".base64_encode($imageLink);
	}

	// Include the template files
	require ROOT."/tpl/_include.php";
}
?>
