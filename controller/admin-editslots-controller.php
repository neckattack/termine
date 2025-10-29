<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */

/**
 * Check if current user is allowed to edit this date
 * @param  int $user The user ID
 * @param  int $date The date ID
 * @return bool $allowed If allowed
 */
function checkIfAllowed($user, $date) {
	global $DB;
	$user      = (int) $user;
	$date      = (int) $date;
	$group_id  = 0;
	$group_ids = array();
	$assocs    = array();

	// The group id and contacts of the client
	$sql  = "SELECT `c`.`id`, `c`.`group_id`, `c`.`contact_masseur_id`, `c`.`contact_client_id` \n";
	$sql .= "FROM `dates` AS `d` \n";
	$sql .= "LEFT JOIN `clients` AS `c` ON (`c`.`id` = `d`.`client_id`) \n";
	$sql .= "WHERE `d`.`id` = :date \n";
	$params = array("date" => $date);
	$client = $DB->PreparedSelect($sql, $params);
	$group_id  = (int) $client[0]["group_id"];
	$client_id = (int) $client[0]["id"];

	// Get all group members
	$sql = "SELECT `group_id` FROM `group_members` WHERE `admin_id` = :user";
	$params = array("user" => $user);
	$res = $DB->PreparedSelect($sql, $params);
	foreach ($res AS $entry) {
		$id = (int) $entry["group_id"];
		$group_ids[$id] = $id;
	}

	// Directly associated users
	$sql = "SELECT `admin_id` FROM `client_members` WHERE `client_id` = :client";
	$params = array("client" => $client_id);
	$res = $DB->PreparedSelect($sql, $params);
	foreach ($res AS $entry) {
		$id = (int) $entry["admin_id"];
		$assocs[$id] = $id;
	}
	



	// Super admins may edit anything
	if (isset($group_ids[1])) {
		return true;
	}

	// Contacts
	if ($user === (int)$client[0]["contact_masseur_id"] || $user === (int)$client[0]["contact_client_id"]) {
		return true;
	}

	// Directly associated
	if (isset($assocs[$user])) {
		return true;
	}

	// Return true if user is a member of the group the client is associated with
	if (isset($group_ids[$group_id])) {
		return true;
	}

	// Not found, not allowed
	return false;
}



/**
 * Check if current user is allowed to edit this reservation entry
 * @param  int $user The user ID
 * @param  int $resid The reservation ID
 * @return bool $allowed If allowed
 */
function checkIfAllowed_Reservation($user, $resid) {
	global $DB;
	$user      = (int) $user;
	$resid     = (int) $resid;
	$group_id  = 0;
	$group_ids = array();


	// Assigned to group
	$sql  = "SELECT `c`.`id`, `c`.`group_id`, `c`.`contact_masseur_id`, `c`.`contact_client_id` \n";
	$sql .= "FROM `reservations` AS `r` \n";
	$sql .= "LEFT JOIN `times` AS `t` ON (`t`.`id` = `r`.`time_id`) \n";
	$sql .= "LEFT JOIN `dates` AS `d` ON (`d`.`id` = `t`.`date_id`) \n";
	$sql .= "LEFT JOIN `clients` AS `c` ON (`c`.`id` = `d`.`client_id`) \n";
	$sql .= "WHERE `r`.`id` = :resid \n";
	$params   = array("resid" => $resid);
	$res      = $DB->PreparedSelect($sql, $params);
	$client   = $res[0];
	$client_id = (int) $client["id"];
	$group_id = (int) $client["group_id"];

	$sql = "SELECT `group_id` FROM `group_members` WHERE `admin_id` = :user";
	$params = array("user" => $user);
	$res = $DB->PreparedSelect($sql, $params);
	foreach ($res AS $entry) {
		$id = (int) $entry["group_id"];
		$group_ids[$id] = $id;
	}

	// Directly associated users
	$sql = "SELECT `admin_id` FROM `client_members` WHERE `client_id` = :client";
	$params = array("client" => $client_id);
	$res = $DB->PreparedSelect($sql, $params);
	foreach ($res AS $entry) {
		$id = (int) $entry["admin_id"];
		$assocs[$id] = $id;
	}


	// Super admins may edit anything
	if (isset($group_ids[1])) {
		return true;
	}

	// Return true if user is a member of the group the client is associated with
	if (isset($group_ids[$group_id])) {
		return true;
	}

	// Directly associated
	if (isset($assocs[$user])) {
		return true;
	}

	// Client or masseur contact
	if ($user === (int) $client["contact_masseur_id"] || $user === (int) $client["contact_client_id"]) {
		return true;
	}

	// Not found, not allowed
	return false;
}



/**
 * Get the client id from a date id
 * @param  int $date_id   The date id
 * @return int $client_id The client id
 */
function getClientId($date_id) {
	global $DB;
	global $config;
	$id = (int) $date_id;

	$sql  = "SELECT `c`.`id` AS `client_id` \n";
	$sql .= "FROM `dates` AS `d` ";
	$sql .= "LEFT JOIN `clients` AS `c` ON (`d`.`client_id` = `c`.`id`) ";
	$sql .= "WHERE `d`.`id` = :id ";
	$sql .= "LIMIT 1";

	$params = array("id" => $id);
	$client = $DB->PreparedSelect($sql, $params, false, false);
	
	if (!isset($client[0]["client_id"])) {
		return false;
	}

	return (int) $client[0]["client_id"];
}



/**
 * Get infos for a day
 * @param  $id      int    ID for the day
 * @return $client  array  Client information
 */
function getDayInfos($id) {
	global $DB;
	global $config;
	$id = (int) $id;

	$dateStr = getMySQLDateString($config["dateFormat"]);
	$sql  = "SELECT `c`.`id` AS `client_id`, `c`.`name` AS `client_name`, `c`.`default_diagnosis`, `c`.`default_service_ids`, `d`.`id` AS `date_id`, DATE_FORMAT(`d`.`date`, '".$dateStr."') AS `date`, `t`.`id` AS `time_id`, TIME_FORMAT(`t`.`time_start`, '%H:%i') AS `time_start`, TIME_FORMAT(`t`.`time_end`, '%H:%i') AS `time_end`, `r`.`id` AS `res_id`, `r`.`name` AS `res_name`, `r`.`email` ";
	$sql .= "FROM `dates` AS `d` ";
	$sql .= "LEFT JOIN `times` AS `t` ON (`d`.`id` = `t`.`date_id`) ";
	$sql .= "LEFT JOIN `reservations` AS `r` ON (`t`.`id` = `r`.`time_id`) ";
	$sql .= "LEFT JOIN `clients` AS `c` ON (`d`.`client_id` = `c`.`id`) ";
	$sql .= "WHERE `d`.`id` = :id ";
	$sql .= "ORDER BY `t`.`time_start` ";
	
	$params = array("id" => $id);
	$day = $DB->PreparedSelect($sql, $params, false, false);
	
	if (!isset($day[0]["client_id"])) {
		return false;
	}
	
	return $day;
}



/**
 * Delete a reservation
 * @param  $id      int  The ID of the reservation
 * @return $success int  Number of deleted rows (n on success, 0 on failure)
 */
function deleteReservation($id) {
	global $DB;
	$id = (int)$id;
	
	$sql  = "DELETE `reservations`.*  ";
	$sql .= "FROM `reservations` ";
	$sql .= "WHERE `reservations`.`id` = :id ";
	$params = array("id" => $id);
	
	$delete = $DB->PreparedStatement($sql, $params, false, false);
	
	return $delete;
}



/**
 * Generate time values for a date
 * @param  $date_id     int    The id of the date
 * @param  $durations   array  Array with the durations in minutes of one appointment for each block
 * @param  $startTimes  array  Array with the starting times for each block
 * @param  $endTimes    array  Array with the ending times for each block
 * @return $insert      mixed  FALSE on error or amount of inserted rows
 */
function generateTimeValues($date_id, $durations, $startTimes, $endTimes) {
	global $DB;
	global $config;
	
	$insert  = false;
	$date_id = (int)$date_id;
	$times   = array();
	
	// No arrays
	if (!is_array($durations) || !is_array($startTimes) || !is_array($endTimes)) {
		return false;
	}
	
	$cDur   = count($durations);
	$cStart = count($startTimes);
	$cEnd   = count($endTimes);
	
	
	// No entries in array
	if ( $cDur == 0 || $cStart == 0 || $cEnd == 0) {
		return false;
	}
	
	// Different amount of entries
	if ( $cDur != $cStart || $cDur != $cEnd || $cStart != $cEnd) {
		return false;
	}
	
	// No end time can be higher than any of the following start times
	foreach ($endTimes AS $key => $endTime) {
		$startTimesTemp = array_merge(array_slice($startTimes, $key+1), array(24));
		if ($endTime > min($startTimesTemp)) {
			return false;
		}
		
	}
	
	
	// Go through all the blocks
	for ($x=0; $x<$cDur; $x++) {
		$duration  = ($durations[$x]);
		
		// No 0 minutes durations
		if ($duration < 1) {
			continue;	// or break;?
		}
		
		$startTime = $startTimes[$x].":00";	// Add minutes, or if set, seconds. strtotime() needs the minutes set. And we don't really care about the seconds
		$endTime   = $endTimes[$x].":00";	// Add minutes, or if set, seconds
		
		$startTimeStamp = strtotime($startTime);
		$endTimeStamp   = strtotime($endTime);
		
		$startHour = (int)date("H", $startTimeStamp);
		$endHour   = (int)date("H", $endTimeStamp);
		
		
		// Calculate the number of entries
		$minutes = ($endTimeStamp - $startTimeStamp) / 60;
		$amount  = floor($minutes / $duration);
		
		
		// Calculate the time values
		for ($i=0; $i<$amount; $i++) {
			$curStartTime = $startTimeStamp + $i * $duration*60;
			$curEndTime   = $curStartTime + $duration*60;
			
			// Break if new starting time is lower than old starting time (meaning a date switch happened)
			$curStartHour = (int)date("H", $curStartTime);
			if ($curStartHour < $startHour) break;
			
			// Break if new starting time is higher than end time
			// Break if new ending time is higher then end time
			if ($curStartTime > $endTimeStamp) break;
			if ($curEndTime   > $endTimeStamp) break;
			
			// Enter the time values
			$times[] = array(
				"start" => formatTime($curStartTime, $config["timeFormat"]),
				"end"   => formatTime($curEndTime,   $config["timeFormat"])
			);
		}
	}
	
	
	// Proceed only if there were actually times entered
	$count = count($times);
	if ($count > 0) {
		// Delete all already existing times and reservations for this date
		/*
		$sql  = "DELETE `times`.*, `reservations`.*  ";
		$sql .= "FROM `times` ";
		$sql .= "LEFT JOIN `reservations` ON (`times`.`id` = `reservations`.`time_id`) ";
		$sql .= "WHERE `times`.`date_id` = :id ";
		$params = array("id" => $date_id);
		$delete = $DB->PreparedStatement($sql, $params, false, false);
		
		if ($delete === false || is_null($delete)) {
			return false;
		}
		*/
		
		// Add new times into database
		$sqlAdd = array();
		$sqlStr = "";
		$params = array(
			"id"   => $date_id,
			"user" => $_SESSION["userid"]
		);
		
		for ($x=0; $x<$count; $x++) {
			$sqlAdd[] = ":id, :start_".$x.", :end_".$x.", :user, NOW()";
			$params["start_".$x] = $times[$x]["start"];
			$params["end_".$x]   = $times[$x]["end"];
		}
		$sqlStr = implode("), (", $sqlAdd);
		
		$sql  = "INSERT INTO `times` (`date_id`, `time_start`, `time_end`, `created_by`, `created_at`) VALUES (";
		$sql .= $sqlStr.") ";
		$insert = $DB->PreparedStatement($sql, $params, false, false);

		// Error
		if ($insert === false || is_null($insert)) {
			return false;
		}
	}
	
	return $insert;
}


/**
 * Delete a time entry
 * Will also delete any associated reservation
 * @param  $id int The id of the time value
 * @return bool
 */
function deleteTimeEntry($id) {
	global $DB;
	global $config;
	
	$sql  = "DELETE `times`.*, `reservations`.*  ";
	$sql .= "FROM `times` ";
	$sql .= "LEFT JOIN `reservations` ON (`times`.`id` = `reservations`.`time_id`) ";
	$sql .= "WHERE `times`.`id` = :id ";
	$params = array("id" => (int)$id);
	$delete = $DB->PreparedStatement($sql, $params, false, false);
	

	if ($delete === false || is_null($delete)) {
		return false;
	}

	return true;
}


/**
 * Check if a new slot may be entered
 * @param  $startTimes array  All start and end times
 * @param  $date_id    int    The id of the date where the block should be added
 * @return $result
 *     		"allow"    bool   If the block can be added (i.e. if it is not yet allocated)
 *			"error"    array  Rows that contain an error
 */
function checkIfAvailable($times, $date_id) {
	global $DB;
	global $config;

	$error  = 0;
	$errors = array();
	$result = array();
	$params = array(
		"date_id" => (int) $date_id
	);

	// All start times
	// An entry is only valid if there's a start and an end time
	foreach ($times["start"] AS $key => $start) {
		$start = $times["start"][$key];
		$end   = $times["end"][$key];

		// We need hours and minutes
		$arrStart = explode(":", $start);
		$arrEnd   = explode(":", $end);

		$params["hourStart"]   = $arrStart[0];
		$params["minuteStart"] = isset($arrStart[1]) ? $arrStart[1] : 0;
		$params["hourEnd"]     = $arrEnd[0];
		$params["minuteEnd"]   = isset($arrEnd[1]) ? $arrEnd[1] : 0;

		$sql  = "SELECT `t`.`time_start`, `t`.`time_end` \n";
		$sql .= "FROM `times` AS `t` \n";

		$sql .= "WHERE `t`.`date_id` = :date_id AND (( \n";
		$sql .= "     MAKETIME(:hourStart, :minuteStart, 0) < `t`.`time_end` \n";
		$sql .= " AND MAKETIME(:hourEnd,   :minuteEnd,   0) > `t`.`time_start` \n";
		$sql .= ") OR ( \n";
		$sql .= "	  MAKETIME(:hourEnd, :minuteEnd, 0)  >  `t`.`time_start` \n";
		$sql .= " AND MAKETIME(:hourEnd, :minuteEnd, 0)  <= `t`.`time_end` \n";
		$sql .= ") OR ( \n";
		$sql .= "	  `t`.`time_start` >= MAKETIME(:hourStart, :minuteStart, 0) \n";
		$sql .= " AND `t`.`time_end`   <  MAKETIME(:hourEnd,   :minuteEnd,   0) \n";
		$sql .= ")) \n";

		$sql .= "ORDER BY `t`.`time_start` ASC \n";
		
		$res  = $DB->PreparedSelect($sql, $params, false, false, false);

		// Found a result, cannot use these time values
		if (count($res) > 0) {
			#print_r($params);
			#print_r($res);
			$error++;
			$errors[$key] = $key;
		}
	}

	
	#var_dump($times_start);
	#var_dump($times_end);

	if ($error > 0) {
		$result["errors"] = $errors;
		$result["allow"]  = false;
	}
	else {
		$result["allow"]  = true;
	}
	return $result;
}

?>