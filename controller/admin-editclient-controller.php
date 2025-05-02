<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
require ROOT."/inc/email_delete_day_text.php";	// The detele day e-mail default text
/**
 * Get infos for a client
 * @param  $id      int    ID for the client
 * @return $client  array  Client information
 */
function getClientInfos($id) {
	global $DB;
	global $config;
	$id = (int)$id;

	$sql  = "SELECT `c`.`id`, `c`.`name`, `c`.`hashlink`, `c`.`greeting_text`, `c`.`email_text`, \n";
	$sql .= "`c`.`contact_masseur_id`, `c`.`contact_client_id`, `c`.`enabled`, `c`.`group_id`, `c`.`image`, `c`.`price`, `c`.`one_time_booking` ";
	$sql .= "FROM `clients` AS `c` ";
	$sql .= "WHERE `c`.`id` = :id ";

	$params = array("id" => $id);
	$client = $DB->PreparedSelect($sql, $params, false, false);

	if (!isset($client[0]["id"])) {
		return false;
	}
	$client = $client[0];


	// Get all associated dates for this client
	$sql = "SELECT `id`, `date`, `masseur_id` FROM `dates` WHERE `client_id` = :id ORDER BY `date` ASC";
	$dates = $DB->PreparedSelect($sql, $params, false, false);


	$client["dates"] = array();
	foreach ($dates AS $date) {
		$client["dates"][] = array(
			"id"   => $date["id"],
			"date" => formatDate($date["date"], $config["dateFormat"]),
			"masseur_id" => $date["masseur_id"],
		);
	}

	// Get all associated users for this client
	$client["contacts"] = array(
		"groups"   => array(),
		"direct"   => array(),
		"mixed"    => array(),
		"contacts" => array(),
	);



	// Get all associated group users for this client
	$sql  = "SELECT `a`.`id`, `a`.`username`, `a`.`first_name`, `a`.`last_name`, `a`.`email` \n";
	$sql .= "FROM `group_members` AS `m` \n";
	$sql .= "LEFT JOIN `admin` AS `a` ON (`a`.`id` = `m`.`admin_id`) \n";
	$sql .= "WHERE `m`.`group_id` = :group_id";
	$params = array("group_id" => $client["group_id"]);
	$contacts = $DB->PreparedSelect($sql, $params, false, false);

	if (is_array($contacts)) {
		foreach ($contacts AS $contact) {
			$client["contacts"]["groups"][$contact["id"]] = $contact;
			$client["contacts"]["mixed"][$contact["id"]] = $contact;
		}
	}


	// Get all directly associated users for this client
	$sql  = "SELECT `a`.`id`, `a`.`username`, `a`.`first_name`, `a`.`last_name`, `a`.`email` \n";
	$sql .= "FROM `client_members` AS `m` \n";
	$sql .= "LEFT JOIN `admin` AS `a` ON (`a`.`id` = `m`.`admin_id`) \n";
	$sql .= "WHERE `m`.`client_id` = :client_id";
	$params = array("client_id" => $client["id"]);
	$contacts = $DB->PreparedSelect($sql, $params, false, false);

	// Only add those not already present to the mixed array
	if (is_array($contacts)) {
		foreach ($contacts AS $contact) {
			$client["contacts"]["direct"][$contact["id"]] = $contact;

			if (!isset($client["contacts"][$contact["id"]])) {
				$client["contacts"]["mixed"][$contact["id"]] = $contact;
			}
		}
	}


	// And add the contacts
	$clients["contacts"]["contacts"][$client["contact_masseur_id"]] = $client["contact_masseur_id"];
	$clients["contacts"]["contacts"][$client["contact_client_id"]]  = $client["contact_client_id"];
	$clients["contacts"]["mixed"][$client["contact_masseur_id"]] = $client["contact_masseur_id"];
	$clients["contacts"]["mixed"][$client["contact_client_id"]]  = $client["contact_client_id"];



	return $client;
}



/**
 * Generate a random hash for a client
 * @param  $length  int     (optional) The length of the hashlink
 * @return $hash    string  The hash
 */
function generateHash($length=5) {
	// Upper case letters (no "o")
	// 1-9 (no "0")

	global $DB;
	$characters = array_merge(range(1, 9), array_merge(range("a", "n"),	range("p", "z")));
	$cLen = count($characters);
	$hash = "";

	for ($p=0; $p<$length; $p++) {
		$hash .= strtoupper($characters[mt_rand(0, ($cLen-1))]);
	}

	// Check if it already exists in the database
	$sql = "SELECT `id` FROM `clients` WHERE `hashlink` = :hash";
	$result = $DB->PreparedSelect($sql, array("hash" => $hash), false, false);

	// If it is, generate a new one
	if (isset($result[0]["id"])) {
		$hash = generateHashLink($length);
	}

	return $hash;
}



/**
 * Generate a random hash link for a client
 * @param  $hash    string (optional) An existing hash
 * @param  $length  int    (optional) The length of the hashlink
 * @return $hash    array  Array with "hash" and "hashlink", the latter bing the full URL to this client
 */
function generateHashLink($hash="", $length=5) {
	if (!isset($hash[0])) {
		$hash = generateHash($length);
	}

	return array(
		"hash"     => $hash,
		"hashlink" => ABSURL.$hash
	);
}



/**
 * Edit a client's data
 * @param  $data     array  Array with the client data
 * @return $success  mixed  ID of the changed client or FALSE on error
 */
function editClient($data) {
	global $DB;
	global $config;

	$id                 = (int) $data["cid"];
	$isEdit             = ($id > 0);	// true/false
	$name               = @trim($data["cName"]);	// Cannot be changed for edit client, so not available there
	$hash               = $data["cHash"];
	$days               = (isset($data["cDays"])) ? $data["cDays"] : array();		// May be empty
	$text               = trim($data["cText"]);
	$mailtext           = trim($data["emailText"]);
	$contact_masseur_id = (int) $data["contact_masseur_id"];
	$contact_client_id  = (int) $data["contact_client_id"];
	$enabled            = @(int)$data["cEnabled"];
	$group_id           = (isset($data["group_id"])) ? (int) $data["group_id"] : 0;
	$price				= $data["price"];
	$one_time_booking = isset($data["one_time_booking"]) ? (int)$data["one_time_booking"] : 0;
	$user_ids           = (isset($data["user_ids"])) ? $data["user_ids"] : array();
	$existing_contacts  = false;
	$params             = array(
		"text"               => $text,
		"mailtext"           => $mailtext,
		"contact_masseur_id" => $contact_masseur_id,
		"contact_client_id"  => $contact_client_id,
		"enabled"            => $enabled,
		"group_id"           => $group_id,
		"price"				 => $price,
		"one_time_booking" => $one_time_booking,
	);




	// Edit a client or Add a client?
	switch ($id) {
		// Add
		case 0:
			$sql  = "INSERT INTO `clients` (`name`, `hashlink`, `greeting_text`, `email_text`, `contact_masseur_id`, `contact_client_id`, `enabled`, `created_by`, `created_at`, `group_id`, `price`, `one_time_booking`) \n";
			$sql .= "VALUES (:name, :hash, :text, :mailtext, :contact_masseur_id, :contact_client_id, :enabled, :user, NOW(), :group_id, :price, :one_time_booking)";

			$params["name"] = $name;
			$params["hash"] = $hash;
			$params["user"] = (int) $_SESSION["userid"];
		break;

		// Edit
		default:
			// Check for existing associated users
			$sql = "SELECT `contact_masseur_id`, `contact_client_id` FROM `clients` WHERE `id` = :id";
			$res = $DB->PreparedSelect($sql, array("id" => $id), false, false);
			$existing_contacts = $res[0];


			$sql  = "UPDATE `clients` SET \n";
			$sql .= "`greeting_text` = :text, `email_text` = :mailtext, `contact_masseur_id` = :contact_masseur_id, `contact_client_id` = :contact_client_id, \n";
			$sql .= "`name` = :name, `enabled` = :enabled, `group_id` = :group_id, `price` = :price, `one_time_booking` = :one_time_booking  \n";
			$sql .= "WHERE `id` = :id";

			$params["id"] = $id;
			$params["name"] = $name;
		break;
	}

	// Insert into / update the database
	$clientRows = $DB->PreparedStatement($sql, $params, false, false);

	// Error
	if ($clientRows === false || is_null($clientRows)) {
		return false;
	}

	// Set the client id if new client
	$id = ($id > 0) ? $id : $DB->lastInsertId();


	/**
	 * REMOVED
	 * We do not want the user to be deleted from the member list if he is deleted as a contact!
	 */
	/*
	// Check for associated users and update if necessary
	if ($existing_contacts !== false) {
		$masseur_old = (int) $existing_contacts["contact_masseur_id"];
		$client_old  = (int) $existing_contacts["contact_client_id"];

		$masseur_new = (int) $params["contact_masseur_id"];
		$client_new  = (int) $params["contact_client_id"];


		$sql = "DELETE FROM `client_members` WHERE `admin_id` = :user AND `client_id` = :client";
		$params = array(
			"user"   => $masseur_old,
			"client" => $id,
		);

		// Delete masseur contact association
		$res = $DB->PreparedStatement($sql, $params, false, false);


		// Delete client contact association
		$params["user"] = $client_old;
		$res = $DB->PreparedStatement($sql, $params, false, false);

		// And insert new
		$sql  = "INSERT INTO `client_members` (`admin_id`, `client_id`) \n";
		$sql .= "VALUES (:user, :client)";

		// Add masseur contact
		if ($masseur_new > 0) {
			$params["user"] = $masseur_new;
			$DB->PreparedStatement($sql, $params, false, false);
		}
		// Add client contact
		if ($client_new > 0) {
			$params["user"] = $client_new;
			$DB->PreparedStatement($sql, $params, false, false);
		}
	}
	*/

	$masseur_old = (int) @$existing_contacts["contact_masseur_id"];
	$client_old  = (int) @$existing_contacts["contact_client_id"];

	$masseur_new = (int) $params["contact_masseur_id"];
	$client_new  = (int) $params["contact_client_id"];

	// But we want a user to be ADDED to the client members list if he is selected as a contact
	if ($masseur_new > 0 || $client_new > 0) {
		$sql  = "INSERT INTO `client_members` (`admin_id`, `client_id`) \n";
		$sql .= "VALUES (:user, :client)";

		$params = array(
			"client" => $id,
		);


		// Masseur
		if ($masseur_new > 0 && $masseur_old != $masseur_new) {
			$params["user"] = $masseur_new;
			$DB->PreparedStatement($sql, $params, false, false);
		}

		// Client
		if ($client_new > 0 && $client_old != $client_new) {
			$params["user"] = $client_new;
			$DB->PreparedStatement($sql, $params, false, false);
		}
	}


	// Edit directly associated users
	// Note: this is only possible for super admins, because only they can see the user selection
	if ($_SESSION["SUPERADMIN"] === true) {
		$old_users     = array();
		$added_users   = array();
		$deleted_users = array();
		$countAdd      = 0;
		$countDel      = 0;


		// All previously associated users
		$sql = "SELECT `admin_id` FROM `client_members` WHERE `client_id` = :client";
		$params = array(
			"client" => $id,
		);
		$res = $DB->PreparedSelect($sql, $params, false, false);

		// Compare with new list
		// CAUTION: contact_masseur and contact_client should be ALWAYS in the list
		foreach ($res AS $entry) {
			$old_users[] = $entry["admin_id"];
		}

		$added_users   = array_diff($user_ids, $old_users);
		$deleted_users = array_diff($old_users, $user_ids);

		// Users added
		if (count($added_users) > 0) {
			$sql  = "INSERT INTO `client_members` (`admin_id`, `client_id`) \n";
			$sql .= "VALUES (:user, :client)";
			$sqlPrep = $DB->PrepareStatement($sql);

			$params = array(
				"client" => $id,
			);


			foreach ($added_users AS $user) {
				$params["user"] = $user;
				$countAdd += $DB->PreparedStatement($sqlPrep, $params, false, false);
			}
		}

		// Users deleted
		// NOTE: Do NOT delete the users that are assigned as contacts
		if (count($deleted_users) > 0) {
			$sql = "DELETE FROM `client_members` WHERE `admin_id` = :user AND `client_id` = :client";
			$sqlPrep = $DB->PrepareStatement($sql);

			$params = array(
				"client" => $id,
			);


			foreach ($deleted_users AS $user) {
				if ($user == $masseur_new || $user == $client_new) {	// User would be masseuer or client contact, so do not delete
					continue;
				}

				$params["user"] = $user;
				$countDel += $DB->PreparedStatement($sqlPrep, $params, false, false);
			}
		}
	}

	/*
	pre($user_ids);
	pre($old_users);
	pre($added_users);
	pre($deleted_users);
	pre($countAdd);
	pre($countDel);
	*/

	/*
	// If $user_ids === 0, no users whatsoever are associated with this client, so delete them all
	elseif (count($user_ids) === 0 && $masseur_new === 0 && $client_new === 0 && $isEdit === true) {
		// Delete all associated users
		$sql = "DELETE FROM `client_members` WHERE `client_id` = :client";
		$params = array(
			"client" => $id,
		);

		$res = $DB->PreparedStatement($sql, $params, false, false);
	}
	*/



	// Check for new days values and add them

	// Get the MySQL date for the days
	#$newDays = array_map("getMySQLDate", $days);
	$newDays = array();
	foreach ($days AS $day) {
		if (!isset($day[1])) continue;	// Only if it's actually set
		$newDays[] = getMySQLDate($day, $config["dateFormat"]);
	}

	$count = count($newDays);
	if ($count > 0) {

		// Add into the table, only if not already existent
		$sqlAdd = array();
		$sqlStr = "";
		$params = array(
			"id"   => $id,
			"user" => $_SESSION["userid"]
		);

		for ($x=0; $x<$count; $x++) {
			$sqlAdd[] = ":date_".$x.", :id, :user, NOW()";
			$params["date_".$x] = $newDays[$x];
		}
		$sqlStr = implode("), (", $sqlAdd);


		$sql  = "INSERT INTO `dates` (`date`, `client_id`, `created_by`, `created_at`) VALUES (";
		$sql .= $sqlStr.") ";
		$sql .= "ON DUPLICATE KEY UPDATE `id` = `id` ";

		$insert = $DB->PreparedStatement($sql, $params, false, false);

		// Error
		if ($insert === false || is_null($insert)) {
			return false;
		}
	}

	// Return the id of added/edited client on success
	return $id;
}



/**
 * Set the state of a client (enabled, disabled)
 * @param  $id      int  ID of the client
 * @param  $state   int  0/1 (disabled/enabled)
 * @return $update  int  Number of updated rows (1 on success, 0 on failure)
 */
function setClientState($id, $state) {
	global $DB;
	$id  = (int) $id;
	$sql = "UPDATE `clients` SET `enabled` = :state WHERE `id` = :id";
	$params = array(
		"id"    => $id,
		"state" => $state
	);
	$update = $DB->PreparedStatement($sql, $params, false, false);

	return $update;
}

function copyDate($id, $dates, $cid)
{
	global $DB;
	global $config;
	
	foreach($dates as $row) {
		$date = getMySQLDate($row, $config["dateFormat"]);
		$sql    = "INSERT INTO `dates` (`date`, `client_id`, `created_by`, `created_at`) VALUES (:date, :id, :user, NOW())";
		$params = array(
			"date" => $date,
			"id" => $cid,
			"user" => $_SESSION["userid"]
		);

		$add = $DB->PreparedStatement($sql, $params, false, false);
		if ($add === false || is_null($add)) {
			return false;
		}

	$dateID = $DB->lastInsertId();

	if($dateID) {
		$sqlCopy    = "SELECT * FROM times where date_id = :id";
		$params = array("id" => $id);

		$data = $DB->PreparedSelect($sqlCopy, $params, false, false);

		if($data) {
			foreach($data as $timeRow){
				$sql    = "INSERT INTO `times` (`date_id`, `time_start`, `time_end`, `created_by`, `created_at`) VALUES (:date, :time_start, :time_end, :user, NOW())";
				$params = array(
					"date" => $dateID,
					"time_start" => $timeRow['time_start'],
					"time_end" => $timeRow['time_end'],
					"user" => $_SESSION["userid"]
				);

				$add = $DB->PreparedStatement($sql, $params, false, false);
				if ($add === false || is_null($add)) {
					return false;
				}
			}
		}
		
		echo json_encode(['status' => 1, 'message' => 'Done successfully']);
		exit(); 
		} else {
			echo json_encode(['status' => 0, 'message' => 'Something went wrong']);
			exit();
		}
	}
}

/**
 * Delete a date
 * - Delete all time values associated with this day
 * - Delete all reservations associated with any of the time values for this day
 * @param  $id      int  The ID of the date
 * @return $success int  Number of deleted rows (n on success, 0 on failure)
 */
function deleteDate($id) {
	global $DB;
	$id = (int)$id;

	
	$select = "SELECT `reservations`.`name`, `reservations`.`email`, `dates`.`date`, `times`.`time_start`, `times`.`time_end`, `times`.`id` ";
	$select .= "FROM `dates` ";
	$select .= "LEFT JOIN `times` ON (`dates`.`id` = `times`.`date_id`) ";
	$select .= "LEFT JOIN `reservations` ON (`times`.`id` = `reservations`.`time_id`) ";
	$select .= "WHERE `dates`.`id` = :id ";
	$params = array("id" => $id);

	$data = $DB->PreparedSelect($select, $params, false, false);

	// Initialize an empty combined array
	$combinedArray = array();

	foreach ($data as $array) {
		if($array['email']) {
			$email = $array['email'];
			if (!isset($combinedArray[$email])) {
				$combinedArray[$email] = array(
					'name' => $array['name'],
					'email' => $array['email'],
					'dates' => array()
				);
			}

			$combinedArray[$email]['dates'][] = $array['id'];
				
		}
	}

	//echo "<pre>"; print_r($combinedArray); die;

	foreach($combinedArray as $key => $row) {
		sendDeleteDayMail($key, $row['name'], $row['dates']);
	}

	$sql  = "DELETE `dates`.*, `times`.*, `reservations`.*  ";
	$sql .= "FROM `dates` ";
	$sql .= "LEFT JOIN `times` ON (`dates`.`id` = `times`.`date_id`) ";
	$sql .= "LEFT JOIN `reservations` ON (`times`.`id` = `reservations`.`time_id`) ";
	$sql .= "WHERE `dates`.`id` = :id ";
	$params = array("id" => $id);

	$delete = $DB->PreparedStatement($sql, $params, false, false);

	return $delete;
}

/**
 * Add a date
 * @param  $id    int     ID of the client
 * @param  $date  string  Localized date string
 * @return        mixed   Array with number of added rows (1 on success, 0 on failure) and ID of the new date. FALSE on error
 */
function addDate($id, $date) {
	global $DB;
	global $config;
	$id   = (int)$id;
	$date = getMySQLDate($date, $config["dateFormat"]);

	$sql    = "INSERT INTO `dates` (`date`, `client_id`, `created_by`, `created_at`) VALUES (:date, :id, :user, NOW())";
	$params = array(
		"id"   => $id,
		"date" => $date,
		"user" => $_SESSION["userid"]
	);

	$add = $DB->PreparedStatement($sql, $params, false, false);
	if ($add === false || is_null($add)) {
		return false;
	}

	$dateID = $DB->lastInsertId();

	return array(
		"rows" => $add,
		"id"   => $dateID
	);
}



/**
 * Change Masseur for Date
 * @param  $date_id  	int  	ID of the date
 * @param  $masseur_id	int  	ID of the masseur (admin)
 * @return int  $change Number of changed rows (1 on success, 0 on failure)
 */
function changeDateMasseur($date_id, $masseur_id) {
	global $DB;
	global $config;

	$sql = "UPDATE `dates` SET `masseur_id` = :masseur_id WHERE `id` = :date_id";
	$params = array("masseur_id" => (int) $masseur_id, "date_id" => (int) $date_id);
	$change = $DB->PreparedStatement($sql, $params, false, false);

	return $change;
}



/**
 * Add a client to a group
 * @param  int  $group  The group id
 * @param  int  $client The client id
 * @return int  $change Number of changed rows (1 on success, 0 on failure)
 */
function addClientToGroup($group, $client) {
	global $DB;
	$sql = "UPDATE `clients` SET `group_id` = :group WHERE `id` = :client";
	$params = array("group" => (int) $group, "client" => (int) $client);
	$change = $DB->PreparedStatement($sql, $params, false, false);

	return $change;
}



/**
 * Remove a client from a group
 * @param  int  $group  The group id
 * @param  int  $client The client id
 * @return int  $change Number of changed rows (1 on success, 0 on failure)
 */
function removeClientFromGroup($group, $client) {
	global $DB;
	$sql = "UPDATE `clients` SET `group_id` = '0' WHERE `id` = :client";
	$params = array("client" => (int) $client);
	$change = $DB->PreparedStatement($sql, $params, false, false);

	return $change;
}

/**
 * Send a delete day e-mail to the booker
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
function sendDeleteDayMail($email, $name, $times=array(), $message=null, $from="neckAttack Ltd. <termine@neckattack.net>", $subject="Reservierung stornieren - neckAttack", $html=false) {
	global $config;
	global $email_delete_day_default_text;

	// Get the message. Fall back to $email_reminder_default_text defined in /inc/email_reminder_text.php
	$message      = (is_null($message) || !isset($message[1])) ? htmlspecialchars_decode(stripslashes($email_delete_day_default_text)) : htmlspecialchars_decode(stripslashes($message));
	$matches      = array();
	$message_time = "";
	$timeStr      = implode("', '", $times);
	$timeFormat   = array();

	// Get date for the time values
	$count = count($times);
	if ($count > 0) {
		if ( ($timeFormat = getTimeFormatForEmail($config["dateFormat"], $timeStr)) === false ) {
			return false;
		}
	}

	if ($html === true) {
		
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

		// The variables to look for
		// And their values
		$variables = array(
			"Benutzername"    => $name,
			"Termine"         => $message_time,
			
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

	$success = sendMail($email, $name, $from, $subject, $message, null, false);

	return $success;
}

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
	$sql .= "TIME_FORMAT(`t`.`time_end`, '%H:%i') AS `time_end` \n";
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
			"end"   => $row["time_end"]
		);
	}

	return $timeFormat;
}

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

function dd($data) {
	echo "<pre>"; print_r($data); die;
}
?>
