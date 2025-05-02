<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Retrieve all clients for a user and/or user's group
 * @param  int   $group_id An optional group_id
 * @return array $clients  The clients
 */
function getAllClients($group_id=null) {
	global $DB;

	$sqlWhere = "";
	$params   = array();


	// No super admins
	// Show only the clients belonging to this user or user's groups
	// Or users associated directly with the client
	if ($_SESSION["SUPERADMIN"] !== true) {

		/*** GROUPS ***/
		// Default display
		$sql    = "SELECT `group_id` FROM `group_members` WHERE `admin_id` = :user";
		$params = array("user" => (int) $_SESSION["userid"]);
		$res    = $DB->PreparedSelect($sql, $params, false, false);
		$arr    = array();

		if (is_array($res)) {
			foreach ($res AS $entry) {
				$arr[$entry["group_id"]] = $entry["group_id"];
			}
		}

		// Add at least one empty/invalid entry, or else the SQL query will fail miserably (specifically the IN () clause)
		if (count($arr) == 0) {
			$arr[] = "'xxx'";
		}
		$inStr_default_groups = implode(", ", $arr);


		// Specific group
		if (!is_null($group_id)) {
			// Check if the user is associated with this group
			$sql = "SELECT COUNT(*) AS `allowed` FROM `group_members` WHERE `group_id` = :group AND `admin_id` = :user";
			$par = array("group" => (int) $group_id, "user" => (int) $_SESSION["userid"]);
			$res = $DB->PreparedSelect($sql, $par, false, false);

			if (isset($res[0]["allowed"]) && $res[0]["allowed"] > 0) {
				$inStr = (int) $group_id;
			}

			// Fallback to default display
			else {
				$inStr = $inStr_default_groups;
			}
		}

		// Default display
		else {
			$inStr = $inStr_default_groups;
		}


		// Also display the clients where the user is directly associated with
		$sql    = "SELECT `client_id` FROM `client_members` WHERE `admin_id` = :user";
		$params = array("user" => (int) $_SESSION["userid"]);
		$res    = $DB->PreparedSelect($sql, $params, false, false);
		$arr    = array();

		if (is_array($res) && count($res) > 0) {
			foreach ($res AS $entry) {
				$arr[$entry["client_id"]] = $entry["client_id"];
			}
		}

		// Add at least one empty/invalid entry, or else the SQL query will fail miserably (specifically the IN () clause)
		if (count($arr) == 0) {
			$arr[] = "'xxx'";
		}

		$inStr2 = implode(", ", $arr);
		$sqlWhere  = "WHERE (`c`.`group_id` IN (".$inStr.") AND `c`.`group_id` != '0') \n";
		$sqlWhere .= "OR `c`.`id` IN (".$inStr2.") \n";
	}


	// Super admins only
	else {
		if (!is_null($group_id)) {
			$sqlWhere = "WHERE `c`.`group_id` = :group_id \n";
			$params["group_id"] = (int) $group_id;
		}
	}

	$today = date('Y-m-d');
	$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'alphabetical';

	if($sortBy == 'upcoming') {
		$sql  = "SELECT `c`.`id`, TRIM(`c`.`name`) AS `name`, `c`.`hashlink`, `c`.`enabled`, `d`.`date` AS `first`, `d`.`date` AS `last`, `d`.`id` AS `date_id`\n";
		$sql .= "FROM `clients` AS `c` \n";
		$sql .= "LEFT JOIN `dates` AS `d` ON (`c`.`id` = `d`.`client_id`) \n";
		$sql .= $sqlWhere . " AND `d`.`date` >='" . $today . "' \n" ;
		$sql .= "GROUP BY `d`.`client_id`, `d`.`date` \n";
		$sql .= "ORDER BY `d`.`date` ASC ";
	} else {
		$sql  = "SELECT `c`.`id`, TRIM(`c`.`name`) AS `name`, `c`.`hashlink`, `c`.`enabled`, MIN(`d`.`date`) AS `first`, MAX(`d`.`date`) AS `last` \n";
		$sql .= "FROM `clients` AS `c` \n";
		$sql .= "LEFT JOIN `dates` AS `d` ON (`c`.`id` = `d`.`client_id`) \n";
		$sql .= $sqlWhere;
		$sql .= "GROUP BY `c`.`id` \n";
		$sql .= "ORDER BY TRIM(`c`.`name`) ASC ";
	}
	
	$params = array();
	$clients = $DB->PreparedSelect($sql, $params, false, false);

	/*if($sortBy == 'upcoming') {
		//Getting total appointments for each date
		foreach($clients as $key => $client) {
			$sql = "SELECT COUNT(*) as total_appointments FROM `times` where date_id=" . $client['date_id'];
			if($client['date_id']) {
				$counts = $DB->PreparedSelect($sql, $params, false, false);
				if($counts) {
					$clients[$key]['total_appointments'] = $counts[0]['total_appointments'];
				} else {
					$clients[$key]['total_appointments'] = 0;
				}
			} else {
				$clients[$key]['total_appointments'] = 0;
			}
			
		}
	}*/
	
	if($sortBy == 'upcoming') {
		//Getting total appointments for each date
		foreach($clients as $key => $client) {
			$sql = "SELECT COUNT(*) as total_appointments FROM `times` where date_id=" . $client['date_id'];
			$sql1 = "SELECT COUNT(*) as booked FROM `times` INNER join reservations on reservations.time_id = times.id AND reservations.name NOT LIKE '%Pause%' AND reservations.name NOT LIKE '%Puase%' where times.date_id= " . $client['date_id'];
			$sql2 = "SELECT SUM(TIMESTAMPDIFF(MINUTE, times.time_start, times.time_end)) as total_booked_time 
                 FROM `times`
                 INNER JOIN reservations ON reservations.time_id = times.id
				 AND reservations.name NOT LIKE '%Pause%' AND reservations.name NOT LIKE '%Puase%' 
                 WHERE times.date_id = " . $client['date_id'];
			$sql3 = "SELECT SUM(TIMESTAMPDIFF(MINUTE, times.time_start, times.time_end)) as total_slot_time
                 FROM `times`
                 WHERE date_id=" . $client['date_id'];
			if($client['date_id']) {
				$counts = $DB->PreparedSelect($sql, $params, false, false);
				$countBooked = $DB->PreparedSelect($sql1, $params, false, false);
				$totalBookedTime = $DB->PreparedSelect($sql2, $params, false, false);
				$totalSlotTime = $DB->PreparedSelect($sql3, $params, false, false);
				if($counts) {
					$clients[$key]['total_appointments'] = $counts[0]['total_appointments'];
					$clients[$key]['total_booked'] = $countBooked[0]['booked'];
					$clients[$key]['total_booked_time'] = $totalBookedTime[0]['total_booked_time'] ?? 0;
					$clients[$key]['total_slot_time'] = $totalSlotTime[0]['total_slot_time'] ?? 0;
				} else {
					$clients[$key]['total_appointments'] = 0;
					$clients[$key]['total_booked'] = 0;
					$clients[$key]['total_booked_time'] = 0;
					$clients[$key]['total_slot_time'] = 0;
				}
			} else {
				$clients[$key]['total_appointments'] = 0;
				$clients[$key]['total_booked'] = 0;
				$clients[$key]['total_booked_time'] = 0;
				$clients[$key]['total_slot_time'] = 0;
			}

			$clients[$key]['last'] = '';
			
		}
	}
	
	// 	echo '<pre>';
	// echo "clients data:\n";
	// print_r($clients);
	// echo '</pre>';

	// die();

	return $clients;
}



/**
 * Returns a formatted string for the display in the admin's client list (startDate - endDate)
 * @param  $start  string  The starting date
 * @param  $end    string  The ending date
 * @return mixed  Formatted string (startDate - endDate / startDate / -)
 */
function formatStartEndDate($start="", $end="") {
	global $config;
	$start = (isset($start[0])) ? formatDate($start, $config["dateFormat"]) : $start;
	$end   = (isset($end[0]))   ? formatDate($end, $config["dateFormat"])   : $end;

	if (strlen($start.$end) < 1) {
		return "-";
	}

	if (isset($end[0])) {
		return $start ." - ". $end;
	}

	return $start;
}
?>
