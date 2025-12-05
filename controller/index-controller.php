<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
require ROOT."/inc/email_text.php";	// The e-mail default text
require ROOT."/inc/email_reminder_text.php";	// The e-mail reminder default text
require ROOT."/inc/email_terminate_text.php";	// The e-mail terminate default text
require ROOT."/inc/email_therapist_list_text.php";	// The e-mail therapist_list default text


/**
 * Get infos for a client
 * @param  $id      int    ID for the client
 * @return $client  array  Client information
 */
function getClientInfos($id) {
	global $DB;
	global $config;
	$id = (int)$id;

	    // Tag: stornovorlauf – booking_deadline_hours für Fristlogik ins Frontend laden
    $sql  = "SELECT `c`.`id`, `c`.`name`, `c`.`hashlink`, `c`.`greeting_text`, `c`.`email_text`, `c`.`contact_masseur_id`, `c`.`enabled`, `c`.`group_id`, `c`.`image`, `c`.`price`, `c`.`booking_deadline_hours`, `c`.`avoid_double_bookings_mode`, `c`.`patient_billing_required`, `c`.`patient_invoice_flag` ";
	$sql .= "FROM `clients` AS `c` ";
	$sql .= "LEFT JOIN `admin` AS `a` ON (`a`.`id` = `c`.`contact_masseur_id`) ";
	$sql .= "WHERE `c`.`id` = :id ";

	$params = array("id" => $id);
	$client = $DB->PreparedSelect($sql, $params, false, false);

	if (!isset($client[0]["id"])) {
		return false;
	}

	return $client[0];
}



/**
 * Get contact info for a client's contact
 * @param  $id      int    The id of the client
 * @return $contact array  Array with the information (name, gender, mail, phone)
 */
function getContactInfo($id) {
	global $DB;
	$id = (int) $id;

	$sql  = "SELECT `a`.`id`, `a`.`gender`, `a`.`first_name`, `a`.`last_name`, `a`.`email`, `a`.`phone` \n";
	$sql .= "FROM `clients` AS `c` \n";
	$sql .= "LEFT JOIN `admin` AS `a` ON (`a`.`id` = `c`.`contact_masseur_id`) \n";
	$sql .= "WHERE `c`.`id` = :id";
	$contact = $DB->PreparedSelect($sql, array("id" => $id), false, false);

	if (!isset($contact[0]["id"])) {
		return array();
	}

	return $contact[0];
}



/**
 * Get contact (admin \ massuer) info by ID
 * @param  $id      int    The id of the admin
 * @return $contact array  Array with the information (name, gender, mail, phone)
 */
function getContactBySelfId($id) {
	global $DB;
	$id = (int) $id;

	$sql  = "SELECT `id`, `username`, `gender`, `first_name`, `last_name`, `email`, `phone` \n";
	$sql .= "FROM `admin` \n";
	$sql .= "WHERE `id` = :id";
	$contact = $DB->PreparedSelect($sql, array("id" => $id), false, false);

	if (!isset($contact[0]["id"])) {
		return array();
	}

	return $contact[0];
}



/**
 * Retrieve all dates for a client
 * @param  $id       int    The client id
 * @param  $initial  bool   If it's an initial request, also populate the first time data
 * @param  $alltimes  bool   Also populate all time data
 * @return           mixed  FALSE on error or array(dates, times) with the data
 */
function getDates($id, $initial=false, $alltimes=false) {
	global $config;						// Include the global config
	global $DB;							// The database Object

	$dates = array();
	$times = array();
	$times_assoc = array();

	// Get all dates for this client
	$dateStr = getMySQLDateString($config["dateFormat"]);
	$sql    = "SELECT `d`.`id`, DATE_FORMAT(`d`.`date`, '".$dateStr."') AS `date`, `d`.`masseur_id`, `a`.`first_name` AS masseur_first_name ";
	$sql   .= "FROM `dates` AS `d` ";
	$sql   .= "RIGHT JOIN `times` AS `t` ON (`d`.`id` = `t`.`date_id`) ";		// Select only those with time values
	$sql   .= "LEFT JOIN `admin` AS `a` ON (`a`.`id` = `d`.`masseur_id`) ";
	$sql   .= "WHERE `d`.`client_id` = :id AND `d`.`date` >= DATE(NOW()) ";
	$sql   .= "GROUP BY `d`.`id` ";
	$sql   .= "ORDER BY `d`.`date` ASC ";

	$params = array("id" => $id);
	$dates  = $DB->PreparedSelect($sql, $params, false, false);

	if (!isset($dates[0]["id"])) {
		return false;
	}


	if ($initial === true) {
		// Get the times for the first date as the initial value
		$times = getTimesForDate($dates[0]["id"]);
	}

	if ($alltimes === true) {
		foreach ($dates as $date) {
			$times_assoc[$date['id']] = getTimesForDate($date['id']);
		}
	}


	return array(
		"dates" => $dates,
		"times" => $times,
		"times_assoc" => $times_assoc
	);
}



/**
 * Returns an array of time values for a specific date
 * @param  $id     int    The ID value of a date
 * @return $times  array  The array with the time values
 */
function getTimesForDate($id) {
	global $DB;	// The database Object

	$sql    = "SELECT `t`.`id`, TIME_FORMAT(`t`.`time_start`, '%H:%i') AS `time_start`, TIME_FORMAT(`t`.`time_end`, '%H:%i') AS `time_end`, `r`.`id` AS `taken`, \n";
	$sql   .= "`d`.`date` FROM `times` AS `t` \n";
	$sql   .= "LEFT JOIN `reservations` AS `r` ON (`t`.`id` = `r`.`time_id`) \n";
	$sql   .= "LEFT JOIN `dates` AS `d` ON (`t`.`date_id` = `d`.`id`) \n";
	$sql   .= "WHERE `date_id` = :id AND CAST(CONCAT_WS(' ', `d`.`date`, `t`.`time_start`) AS DATETIME) >= NOW() \n";
	$sql   .= "ORDER BY `t`.`time_start` ";
	$params = array("id" => $id);
	$times  = $DB->PreparedSelect($sql, $params, false, false);

    $earliestTaken[date('Y-m-d', time() + 23*60*60)] = '23:59:59';
    $earliestTaken[date('Y-m-d', time())] = '23:59:59';
    // Get earliest reservation for next day and today
    foreach ($times AS $time) {
        if ($time['taken']
            && (
                ($time['date'] == date('Y-m-d', time() + 23*60*60) && date('H:i:s') > '19:00:00')
                || ($time['date'] == date('Y-m-d') && $time['time_start'] > date('H:i:s'))
            )
            && $time['time_start'] < $earliestTaken[$time['date']]) {
            $earliestTaken[$time['date']] = $time['time_start'];
        }
    }

    // Setting next day reservation before earliest taken as unavailable
    if ($earliestTaken[date('Y-m-d', time() + 23*60*60)] !== '23:59:59') {
        foreach ($times as $key=>$time) {
            if($time['date'] == date('Y-m-d', time() + 23*60*60) && $time['time_start'] < $earliestTaken[$time['date']]) {
                // do nothing
		// $times[$key]['taken'] = true;
            }
        }
    }

    // Setting current day reservation before earliest taken and before now as unavailable
    if ($earliestTaken[date('Y-m-d', time())] === '23:59:59' || $earliestTaken[date('Y-m-d', time())] < date('H:i:s')) {
        $earliestTaken[date('Y-m-d', time())] = date('H:i:s');
    }
    foreach ($times as $key=>$time) {
        if ($time['date'] == date('Y-m-d') && $time['time_start'] < $earliestTaken[$time['date']]) {
            // do nothing
	    // $times[$key]['taken'] = true;
        }
    }


    return $times;
}



/**
 * Checks if given time values are still available
 * @param  $ids   mixed  Int or array with the ID value(s) of the times
 * @param  $withTakenObj   boolean  Whether taken times array should be returned
 * @return $return  array  Array with "allAvailable" = true/false and "taken" with the IDs of the already taken times
 */
function checkTimesAvailable($ids, $withTakenObj = false) {
	global $DB;	// The database Object
	if (!is_array($ids)) {
		$ids = array($ids);
	}
	$return = array(
		"allAvailable" => false,
		"taken" => array()
	);

	$taken  = array();

	$idStr  = implode("', '", $ids);
    $sql    = "SELECT * FROM `reservations` WHERE `time_id` IN ('".$idStr."')";
	$times  = $DB->PreparedSelect($sql, array(), false, false);

    // Set all taken reservations
	foreach ($times AS $time) {
		$taken[] = $time["time_id"];
	}

	// Set to true if none are taken
	if (count($taken) == 0) {
		$return["allAvailable"] = true;
	}

	$return["taken"] = $taken;
	if ($withTakenObj === true) {
		$return["takenObj"] = $times;
	}

	return $return;
}



/**
 * Set the reservations to the given user
 * @param  $times  array   The array with the times to be reserved
 * @param  $name   string  The name of the user
 * @param  $email  string  The email of the user
 * @return
 */
function setReservations($times, $name, $email) {
	global $DB;	// The database Object

	$count  = count($times);

	if ($count < 1) return false;

	$sqlAdd = array();
	$sqlStr = "";
	$params = array(
		"name"  => $name,
		"email" => $email
	);



	for ($x=0; $x<$count; $x++) {
		$sqlAdd[] = ":name, :email, :app_id_".$x.", NOW()";
		$params["app_id_".$x] = $times[$x];
	}
	$sqlStr = implode("), (", $sqlAdd);



	$sql  = "INSERT INTO `reservations` (`name`, `email`, `time_id`, `registered_at`) VALUES (";
	$sql .= $sqlStr.")";

	$insert = $DB->PreparedStatement($sql, $params, false, false);

	if (!$insert || $insert < 1) {
		return false;
	}

	return array(
		"rows"  => $insert,
		"times" => $times
	);
}


/**
 * Get the reservations by hashed email and times ids
 * @param  $emailHash  string  The emailHash of the user
 * @param  $timesIds   array of ID values of the times
 * @return $reservation
 */
function getReservationsForEmailHash($emailHash, $timesIds) {
	global $DB;	// The database Object

	$idStr  = implode("', '", $timesIds);

	$sql = "SELECT `r`.*, `d`.`client_id`, `d`.`date`, `t`.`date_id`, `t`.`time_start`, `t`.`time_end`";
	$sql .= " FROM `reservations` AS `r`";
	$sql .= " JOIN `times` AS `t` ON (`r`.`time_id` = `t`.`id`) ";
	$sql .= " JOIN `dates` AS `d` ON (`t`.`date_id` = `d`.`id`) ";
	$sql .= " WHERE `r`.`time_id` IN ('".$idStr."')";
	$sql .= " AND `d`.`date` >= CURDATE() "; // include today
	$sql .= " AND md5(`r`.`email`) = '".$emailHash."' ";

	$reservations  = $DB->PreparedSelect($sql, array(), false, false);

	return $reservations;
}


function getAllReservationsForEmailHash($emailHash) {
	global $DB;	// The database Object

	$sql = "SELECT `r`.*, `d`.`client_id`, `d`.`date`, `t`.`date_id`, `t`.`time_start`, `t`.`time_end`";
	$sql .= " FROM `reservations` AS `r`";
	$sql .= " JOIN `times` AS `t` ON (`r`.`time_id` = `t`.`id`) ";
	$sql .= " JOIN `dates` AS `d` ON (`t`.`date_id` = `d`.`id`) ";
	$sql .= " AND `d`.`date` >= CURDATE() "; // include today
	$sql .= " AND md5(`r`.`email`) = '".$emailHash."' ";

	$reservations  = $DB->PreparedSelect($sql, array(), false, false);

	return $reservations;
}


function getSelectedReservation($id) {
	global $DB;	// The database Object

	$sql = "SELECT `r`.*, `d`.`client_id`, `d`.`date`, `t`.`date_id`, `t`.`time_start`, `t`.`time_end`";
	$sql .= " FROM `reservations` AS `r`";
	$sql .= " JOIN `times` AS `t` ON (`r`.`time_id` = `t`.`id`) ";
	$sql .= " JOIN `dates` AS `d` ON (`t`.`date_id` = `d`.`id`) ";
	$sql .= " WHERE `r`.`time_id` IN ('".$id."')";
	

	$reservation  = $DB->PreparedSelect($sql, array(), false, false);
	return $reservation;

}
/**
 * Get timeFormat for emails
 * @return $timeFormat assocc array or false if error
 */
function getTimeFormatForEmail($dateFormat, $timeStr){
	global $DB;
	$timeFormat   = array();

	if ( empty($timeStr) ){
		return $timeFormat;
	}

	$dateStr = getMySQLDateString($dateFormat);
	$sql  = "SELECT \n";
	$sql .= "DATE_FORMAT(`d`.`date`, '".$dateStr."') AS `date`, \n";
	$sql .= "TIME_FORMAT(`t`.`time_start`, '%H:%i') AS `time_start`, \n";
	$sql .= "TIME_FORMAT(`t`.`time_end`, '%H:%i') AS `time_end`, \n";
	$sql .= "`t`.`id` AS `id` \n";
	$sql .= "FROM `times` AS `t` ";
	$sql .= "LEFT JOIN `dates` AS `d` ON (`t`.`date_id` = `d`.`id`) ";
	$sql .= "WHERE `t`.`id` IN ('".$timeStr."') ";
	$sql .= "ORDER BY `d`.`date`, `t`.`time_start` ";
	$select = $DB->PreparedSelect($sql, array(), false, false);

	// Error
	if ($select === false || is_null($select)) {
		return false;
	}

	foreach ($select AS $row) {
		$timeFormat[$row["date"]][] = array(
			"start" => $row["time_start"],
			"end"   => $row["time_end"],
			"id"	=> $row['id']
		);
	}

	return $timeFormat;
}


/**
 * Send a confirmation e-mail to the booker
 * @param  $email    string  The receipient's e-mail address
 * @param  $name     string  The receipient's name
 * @param  $times    array   Array with the time values
 * @param  $contact  array   (optional) Contact information (name, gender, phone, email)
 * @param  $message  string  (optional) The message to send to the user. If not set, will use $email_default_text
 * @param  $from     string  (optional) The sender's e-mail address
 * @param  $subject  string  (optional) Subject for the e-mail
 * @param  $html     bool    (optional) If e-mail is in HTML format
 * @return $success  bool    If e-mail was successfully sent
 */
function sendConfirmationMail($email, $name, $times=array(), $contact=null, $message=null, $from="neckAttack Ltd. <termine@neckattack.net>", $subject="Buchungsbestätigung - neckAttack", $html=false) {
	global $config;
	global $email_default_text;

	// Get the message. Fall back to $email_default_text defined in /inc/email_text.php
	$message      = (is_null($message) || !isset($message[1])) ? htmlspecialchars_decode(stripslashes($email_default_text)) : htmlspecialchars_decode(stripslashes($message));
	$matches      = array();
	$message_time = "";
	$timeStr      = implode("', '", $times);
	$timeFormat   = array();

	// Contact information
	$contact = (is_array($contact)) ? $contact : array();
	$contact["first_name"] = (isset($contact["first_name"])) ? $contact["first_name"]   : "";
	$contact["last_name"]  = (isset($contact["last_name"]))  ? $contact["last_name"]    : "";
	$contact["gender"]     = (isset($contact["gender"]))     ? (int) $contact["gender"] : 0;
	$contact["phone"]      = (isset($contact["phone"]))      ? $contact["phone"]        : $config["phone"];
	$contact["email"]      = (isset($contact["email"]))      ? $contact["email"]        : $config["email"];

	// Build the contract string (e.g. "Gender" "First Name" "Last Name" -> Herr Max Müller)
	$contact["string"]  = "";
	$contact["string"] .= ($contact["gender"] == 1) ? "Herr " : "";
	$contact["string"] .= ($contact["gender"] == 2) ? "Frau " : "";
	$contact["string"] .= (isset($contact["first_name"][0])) ? $contact["first_name"]." " : "";
	$contact["string"] .= (isset($contact["last_name"][0]))  ? $contact["last_name"]      : "";



	// Get date for the time values
	$count = count($times);
	if ($count > 0) {
		if ( ($timeFormat = getTimeFormatForEmail($config["dateFormat"], $timeStr)) === false ) {
			return false;
		}
	}

	if ($html === true) {
		/*
		$message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
		$message .= "<html>\n<head>\n<title>Anmeldung</title>\n</head>\n<body>\n";
		$message .= "<h1>Hallo ".$name."</h1>";
		$message .= "<h2>Sie haben sich erfolgreich für folgende Termine angemeldet:</h2>\n";
		$message .= "<pre>";
		$message .= print_r($times, true);
		$message .= "</pre>";
		$message .= "<body>\n</html>";
		*/
	}

	// Plain text
	// Parse the $email_default_text variable
	else {
		// Get the reservations
		foreach ($timeFormat AS $date => $entries) {
			$message_time .= $date."\n";
			foreach ($entries AS $key => $entry) {
				$message_time .= $entry["start"]." - ".$entry["end"]."\n";
				$message_time .= "ICS-Link: ".ABSURL."web/ics.php?e=".md5($email)."&t=".$entry["id"]."\n";
			}
			$message_time .= "\n";
		}

		// $terminate_link = ABSURL."web/terminate.php?e=".md5($email)."&t=".implode("I", $times);
		$terminate_link = ABSURL."web/bookings.php?e=".md5($email);

		// The variables to look for
		// And their values
		$variables = array(
			"Benutzername"    => $name,
			"Termine"         => $message_time,
			"Telefonnummer"   => $contact["phone"],
			"Ansprechpartner" => $contact["string"],
			"StornierenLink" => $terminate_link,
		);
	}

	// Gett all variables that are contained in <<VAR>>
	preg_match_all("#<<(\w+)>>#i", $message, $matches);


	// Replace the variables with text
	foreach ($matches[1] AS $key) {
		if (!isset($variables[$key])) continue;	// Only those which are defined in $variables

		$pattern = "#<<".$key.">>#i";
		$replace = $variables[$key];
		$message = preg_replace($pattern, $replace, $message);
	}

	// Replace double spaces with one space
	// Fixes double spaces if no masseur name is available
	// But may cause trouble if spaces are used for alignment
	$message = preg_replace("# {2}#", " ", $message);


	$success = sendMail($email, $name, $from, $subject, $message, $contact["email"], false);

	return $success;
}



/**
 * Send a reminder e-mail to the booker
 * @param  $email    string  The receipient's e-mail address
 * @param  $name     string  The receipient's name
 * @param  $times    array   Array with the time values
 * @param  $contact  array   (optional) Contact information (name, gender, phone, email)
 * @param  $message  string  (optional) The message to send to the user. If not set, will use $email_reminder_default_text
 * @param  $from     string  (optional) The sender's e-mail address
 * @param  $subject  string  (optional) Subject for the e-mail
 * @param  $html     bool    (optional) If e-mail is in HTML format
 * @return $success  bool    If e-mail was successfully sent
 */
function sendReminderMail($email, $name, $times=array(), $contact=null, $message=null, $from="neckAttack Ltd. <termine@neckattack.net>", $subject="Erinnerung Massagetermin - neckAttack", $html=false) {
	global $config;
	global $email_reminder_default_text;

	// Get the message. Fall back to $email_reminder_default_text defined in /inc/email_reminder_text.php
	$message      = (is_null($message) || !isset($message[1])) ? htmlspecialchars_decode(stripslashes($email_reminder_default_text)) : htmlspecialchars_decode(stripslashes($message));
	$matches      = array();
	$message_time = "";
	$timeStr      = implode("', '", $times);
	$timeFormat   = array();

	// Contact information
	$contact = (is_array($contact)) ? $contact : array();
	$contact["first_name"] = (isset($contact["first_name"])) ? $contact["first_name"]   : "";
	$contact["last_name"]  = (isset($contact["last_name"]))  ? $contact["last_name"]    : "";
	$contact["gender"]     = (isset($contact["gender"]))     ? (int) $contact["gender"] : 0;
	$contact["phone"]      = (isset($contact["phone"]))      ? $contact["phone"]        : $config["phone"];
	$contact["email"]      = (isset($contact["email"]))      ? $contact["email"]        : $config["email"];

	// Build the contract string (e.g. "Gender" "First Name" "Last Name" -> Herr Max Müller)
	$contact["string"]  = "";
	$contact["string"] .= ($contact["gender"] == 1) ? "Herr " : "";
	$contact["string"] .= ($contact["gender"] == 2) ? "Frau " : "";
	$contact["string"] .= (isset($contact["first_name"][0])) ? $contact["first_name"]." " : "";
	$contact["string"] .= (isset($contact["last_name"][0]))  ? $contact["last_name"]      : "";



	// Get date for the time values
	$count = count($times);
	if ($count > 0) {
		if ( ($timeFormat = getTimeFormatForEmail($config["dateFormat"], $timeStr)) === false ) {
			return false;
		}
	}

	if ($html === true) {
		/*
		$message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
		$message .= "<html>\n<head>\n<title>Anmeldung</title>\n</head>\n<body>\n";
		$message .= "<h1>Hallo ".$name."</h1>";
		$message .= "<h2>Sie haben sich erfolgreich für folgende Termine angemeldet:</h2>\n";
		$message .= "<pre>";
		$message .= print_r($times, true);
		$message .= "</pre>";
		$message .= "<body>\n</html>";
		*/
	}

	// Plain text
	// Parse the $email_default_text variable
	else {
		// Get the reservations
		foreach ($timeFormat AS $date => $entries) {
			$message_time .= $date."\n";
			foreach ($entries AS $key => $entry) {
				$message_time .= $entry["start"]." - ".$entry["end"]."\n";
			}
			$message_time .= "\n";
		}

		// $terminate_link = ABSURL."web/terminate.php?e=".md5($email)."&t=".implode("I", $times);
		$terminate_link = "https://termine.neckattack.net/web/bookings.php?e=".md5($email);

		// The variables to look for
		// And their values
		$variables = array(
			"Benutzername"    => $name,
			"Termine"         => $message_time,
			"Telefonnummer"   => $contact["phone"],
			"Ansprechpartner" => $contact["string"],
			"StornierenLink" => $terminate_link,
		);
	}

	// Gett all variables that are contained in <<VAR>>
	preg_match_all("#<<(\w+)>>#i", $message, $matches);


	// Replace the variables with text
	foreach ($matches[1] AS $key) {
		if (!isset($variables[$key])) continue;	// Only those which are defined in $variables

		$pattern = "#<<".$key.">>#i";
		$replace = $variables[$key];
		$message = preg_replace($pattern, $replace, $message);
	}

	// Replace double spaces with one space
	// Fixes double spaces if no masseur name is available
	// But may cause trouble if spaces are used for alignment
	$message = preg_replace("# {2}#", " ", $message);


	$success = sendMail($email, $name, $from, $subject, $message, $contact["email"], false);

	return $success;
}



/**
 * Send a terminate e-mail to the booker and client
 * @param  $email    string  The receipient's e-mail address
 * @param  $name     string  The receipient's name
 * @param  $times    array   Array with the time values
 * @param  $times_to_add    array   Array with the time values
 * @param  $times_to_delete    array   Array with the time values
 * @param  $contact  array   (optional) Contact information (name, gender, phone, email)
 * @param  $from     string  (optional) The sender's e-mail address
 * @param  $subject  string  (optional) Subject for the e-mail
 * @return $success  bool    If e-mail was successfully sent
 */
function sendTerminateMail($email, $name, $times=array(), $times_to_add=array(), $times_to_delete=array(), $contact=null, $from="neckAttack Ltd. <termine@neckattack.net>", $subject="Buchungsupdate - neckAttack") {
	global $DB;
	global $config;
	global $email_terminate_default_text;

	// $email_terminate_default_text defined in /inc/email_terminate_text.php
	$message      = htmlspecialchars_decode(stripslashes($email_terminate_default_text));
	$matches      = array();
	$message_time = "";
	$timeStr      = implode("', '", $times);
	$timeFormat   = array();

	// Contact information
	$contact = (is_array($contact)) ? $contact : array();
	$contact["first_name"] = (isset($contact["first_name"])) ? $contact["first_name"]   : "";
	$contact["last_name"]  = (isset($contact["last_name"]))  ? $contact["last_name"]    : "";
	$contact["gender"]     = (isset($contact["gender"]))     ? (int) $contact["gender"] : 0;
	$contact["phone"]      = (isset($contact["phone"]))      ? $contact["phone"]        : $config["phone"];
	$contact["email"]      = (isset($contact["email"]))      ? $contact["email"]        : $config["email"];

	// Build the contract string (e.g. "Gender" "First Name" "Last Name" -> Herr Max Müller)
	$contact["string"]  = "";
	$contact["string"] .= ($contact["gender"] == 1) ? "Herr " : "";
	$contact["string"] .= ($contact["gender"] == 2) ? "Frau " : "";
	$contact["string"] .= (isset($contact["first_name"][0])) ? $contact["first_name"]." " : "";
	$contact["string"] .= (isset($contact["last_name"][0]))  ? $contact["last_name"]      : "";



	// Get date for the time values
	$count = count($times);
	if ($count > 0) {
		if ( ($timeFormat = getTimeFormatForEmail($config["dateFormat"], $timeStr)) === false ) {
			return false;
		}

		// Get the reservations
		foreach ($timeFormat AS $date => $entries) {
			$message_time .= $date."\n";
			foreach ($entries AS $key => $entry) {
				$message_time .= $entry["start"]." - ".$entry["end"]."\n";
			}
			$message_time .= "\n";
		}
	} else {
		$message_time .= "Alle Bestellungen storniert!\n\n";
	}

	// Get date for the times_to_add values
	if (false && count($times_to_add)) { // update 08.06.18: not include this to mail
		if ( ($timeFormat = getTimeFormatForEmail($config["dateFormat"], implode("', '", $times_to_add))) === false ) {
			return false;
		}

		$message_time .= "neue Buchungen\n";
		foreach ($timeFormat AS $date => $entries) {
			$message_time .= $date."\n";
			foreach ($entries AS $key => $entry) {
				$message_time .= $entry["start"]." - ".$entry["end"]."\n";
			}
			$message_time .= "\n";
		}
	}

	// Get date for the times_to_delete values
	if (false && count($times_to_delete)) { // update 08.06.18: not include this to mail
		if ( ($timeFormat = getTimeFormatForEmail($config["dateFormat"], implode("', '", $times_to_delete))) === false ) {
			return false;
		}

		$message_time .= "stornierte Buchungen\n";
		foreach ($timeFormat AS $date => $entries) {
			$message_time .= $date."\n";
			foreach ($entries AS $key => $entry) {
				$message_time .= $entry["start"]." - ".$entry["end"]."\n";
			}
			$message_time .= "\n";
		}
	}

	// $terminate_link = ABSURL."web/terminate.php?e=".md5($email)."&t=".implode("I", $times);
	$terminate_link = ABSURL."web/bookings.php?e=".md5($email);

	// The variables to look for
	// And their values
	$variables = array(
		"Benutzername"    => $name,
		"Termine"         => $message_time,
		"Telefonnummer"   => $contact["phone"],
		"Ansprechpartner" => $contact["string"],
		"StornierenLink" => $terminate_link,
	);

	// Gett all variables that are contained in <<VAR>>
	preg_match_all("#<<(\w+)>>#i", $message, $matches);


	// Replace the variables with text
	foreach ($matches[1] AS $key) {
		if (!isset($variables[$key])) continue;	// Only those which are defined in $variables

		$pattern = "#<<".$key.">>#i";
		$replace = $variables[$key];
		$message = preg_replace($pattern, $replace, $message);
	}

	// Replace double spaces with one space
	// Fixes double spaces if no masseur name is available
	// But may cause trouble if spaces are used for alignment
	$message = preg_replace("# {2}#", " ", $message);


	$success = sendMail($email, $name, $from, $subject, $message, $contact["email"], false);

	return $success;
}



/**
 * Send a Therapist List e-mail
 * @param  $massuer    assoc array of Admin\Massuer (Contact)
 * @param  $res_group     array of reservations  The receipient's name
 * @param  $reservations_date  string  String with date to put in message
 * @param  $subject  string  (optional) Subject for the e-mail
 * @param  $from     string  (optional) The sender's e-mail address
 * @return $success  bool    If e-mail was successfully sent
 */
function sendTherapistListMail($massuer, $res_group, $reservations_date, $subject="Anmeldeliste angehangen - neckAttack", $from="neckAttack Ltd. <termine@neckattack.net>") {
	global $DB;
	global $config;
	global $email_therapist_list_default_text;

	if ( !isset($res_group[0]) || !isset($res_group[0]["id"]) ) {
		return false;
	}

	// $email_therapist_list_default_text defined in /inc/email_terminate_text.php
	$message      = htmlspecialchars_decode(stripslashes($email_therapist_list_default_text));
	$matches      = array();
	$csvArray = [];
	$csvString = "";
	$csvFileName = "";
	$message_time = "";

	$email = $massuer["email"];
	$name = trim($massuer["first_name"]." ".$massuer["last_name"]);
	if ( !$name ) {
		$name = $massuer["username"];
	}
	$client_name = $res_group[0]["client_name"] . " (" . $res_group[0]["client_hashlink"] . ")";

	$_tab_del = "\t|\t";
	$message_time .= "Datum: ".$reservations_date."\n\n";
	$csvArray[] = ["#","Datum","Mal starten","Zeitende","Name","Email"];
	$message_time .= "#".$_tab_del."Zeitintervall".$_tab_del."Name".$_tab_del."Email\n";

	// Get the reservations
	foreach ($res_group AS $key => $res) {
		$t_s = substr($res['time_start'], 0, -3);
		$t_e = substr($res['time_end'], 0, -3);
		if(!empty($res['email'])) {
			$message_time .= ($key+1).$_tab_del.$t_s." - ".$t_e.$_tab_del.$res['name'].$_tab_del.$res['email']."\n";
			$csvArray[] = [$key+1,$reservations_date,$t_s,$t_e,$res['name'],$res['email']];
		} else {
			$message_time .= ($key+1).$_tab_del.$t_s." - ".$t_e.$_tab_del.'Still Available'.$_tab_del.''."\n";
			$csvArray[] = [$key+1,$reservations_date,$t_s,$t_e,'Still Available',''];
		}
	}
	$csvFileName = "neckattack--".$reservations_date."--".$res_group[0]["client_hashlink"].".csv";

	// The variables to look for
	// And their values
	$variables = array(
		"Therapist"    	  => $name,
		"Kunde"           => $client_name,
		"Termine"         => $message_time,
	);

	// Gett all variables that are contained in <<VAR>>
	preg_match_all("#<<(\w+)>>#i", $message, $matches);


	// Replace the variables with text
	foreach ($matches[1] AS $key) {
		if (!isset($variables[$key])) continue;	// Only those which are defined in $variables

		$pattern = "#<<".$key.">>#i";
		$replace = $variables[$key];
		$message = preg_replace($pattern, $replace, $message);
	}

	// Replace double spaces with one space
	// Fixes double spaces if no masseur name is available
	// But may cause trouble if spaces are used for alignment
	// $message = preg_replace("# {2}#", " ", $message);

	$success = sendMail($email, $name, $from, $subject, $message, null, false, $csvFileName, $csvArray, 'termine@neckattack.net');

	return $success;
}


/**
 * Create temp csv file to get content
 * @param  $data       array of arrays of stings
 * @return string    Formatted CSV string
 */
function create_csv_string($data) {
  // Open temp file pointer
  if (!$fp = fopen('php://temp', 'w+')) return FALSE;
  // Loop data and write to file pointer
  foreach ($data as $line) fputcsv($fp, $line);
  // Place stream pointer at beginning
  rewind($fp);
  // Return the data
  return stream_get_contents($fp);
}



/**
 * Send out an e-mail
 * @param  $to       string  The receipient's e-mail address
 * @param  $name     string  The receipient's name
 * @param  $from     string  The sender's e-mail address
 * @param  $subject  string  Subject for the e-mail
 * @param  $replyTo  string  (optional) The reply to e-mail address
 * @param  $html     bool    If e-mail is in HTML format
 * @param  $csvFileName     string    CSV file name
 * @param  $csvArray     array of arrays of stings    CSV fields with data
 * @return $success  bool    If e-mail was successfully sent
 */
function sendMail($to, $name, $from, $subject, $message, $replyTo=null, $html=true, $csvFileName=null, $csvArray=null, $cc='') {
        if (defined('DEVELOPMENT_ENVIRONMENT') && DEVELOPMENT_ENVIRONMENT === true) {
            $to = 'termine@neckattack.net';
        }
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Transfer-Encoding: 8bit";
		$headers[] = "X-Priority: 3";
		$headers[] = "X-MSMail-Priority: Normal";
		$headers[] = "X-Mailer: PHP/".phpversion();
		$headers[] = "From: ".$from;

		if (!is_null($replyTo)) {
			$headers[] = "Reply-To: ".$replyTo;
			$headers[] = "Return-Path: ".$replyTo;
		}

		if ( $csvFileName && $csvArray && count($csvArray) ) {

			$multipartSep = '-----'.md5(time()).'-----';

			$headers[] = "Content-Type: multipart/mixed; boundary=\"$multipartSep\"";

			$attachment = chunk_split(base64_encode(create_csv_string($csvArray)));

			$message = "--$multipartSep\r\n"
		        . "Content-Type: text/plain; charset=utf-8\r\n"
		        . "Content-Transfer-Encoding: 8bit\r\n"
		        . "\r\n"
		        . "$message\r\n"
		        . "--$multipartSep\r\n"
		        . "Content-Type: text/csv\r\n"
		        . "Content-Transfer-Encoding: base64\r\n"
		        . "Content-Disposition: attachment; filename=\"$csvFileName\"\r\n"
		        . "\r\n"
		        . "$attachment\r\n"
		        . "--$multipartSep--";

		} else {
			$headers[] = ($html === true) ?
				 "Content-Type: text/html; charset=utf-8" :
				 "Content-Type: text/plain; charset=utf-8";
		}
		if ($cc !== '') {
            $headers[] = 'Cc: ' . $cc;
        }

		// Merge the array into a string
		$headers = implode("\r\n", $headers);

		// Only works with non ASCII chars in the subject
		//$success = mail($to, $subject, $message, $headers);

		// If you want non ASCII chars in the subject as well, use this
		$success = mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);

		return $success;
}
?>
