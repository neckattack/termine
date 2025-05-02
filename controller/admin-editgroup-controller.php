<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */

/**
 * Get infos for a group
 * @param  $id     int    ID for the group
 * @return $group  array  Group information
 */
function getGroupInfos($id) {
	global $DB;
	global $config;

	$id     = (int) $id;
	$params = array("id" => $id);

	$sql  = "SELECT `g`.`group_id`, `g`.`group_name`, `g`.`group_info`, COUNT(`m`.`id`) AS `num_members` ";
	$sql .= "FROM `groups` AS `g` ";
	$sql .= "LEFT JOIN `group_members` AS `m` ON (`m`.`group_id` = `g`.`group_id`) ";
	$sql .= "WHERE `g`.`group_id` = :id ";
	$sql .= "GROUP BY `g`.`group_id` ";
	$sql .= "ORDER BY `g`.`group_id` ";

	$group  = $DB->PreparedSelect($sql, $params, false, false);

	if (!isset($group[0]["group_id"])) {
		return false;
	}

	$group = $group[0];
	$group["members"] = array(
		"users"   => array(),
		"clients" => array(),
	);



	// Get all user members
	$sql  = "SELECT `a`.`id`, `a`.`first_name`, `a`.`last_name`, `a`.`username`, `a`.`email` \n";
	$sql .= "FROM `group_members` AS `m` \n";
	$sql .= "LEFT JOIN `admin` AS `a` ON (`a`.`id` = `m`.`admin_id`) \n";
	$sql .= "WHERE `m`.`group_id` = :id \n";
	$sql .= "ORDER BY `a`.`username` ASC";
	$users = $DB->PreparedSelect($sql, $params, false, false);

	foreach ($users AS $member) {
		$name = "";

		if (isset($member["first_name"][1]) || isset($member["last_name"][1])) {
			$name = $member["last_name"] .", ". $member["first_name"]. " ";
		}
		$name .= "[".$member["username"]."]";

		$group["members"]["users"][] = array(
			"id"       => $member["id"],
			"username" => $member["username"],
			"email"    => $member["email"],
			"name"     => $name,
		);
	}


	// Get all client members
	$sql  = "SELECT `c`.`id`, `c`.`name`, `c`.`hashlink`, `c`.`enabled`, `c`.`group_id` \n";
	$sql .= "FROM `clients` AS `c` \n";
	$sql .= "WHERE `c`.`group_id` = :id \n";
	$sql .= "ORDER BY `c`.`name` ASC";
	$clients = $DB->PreparedSelect($sql, $params, false, false);

	foreach ($clients AS $member) {
		$group["members"]["clients"][] = array(
			"id"       => $member["id"],
			"name"     => $member["name"],
			"hashlink" => $member["hashlink"],
			"enabled"  => $member["enabled"],
		);
	}

	return $group;
}


/**
 * Edit a group
 * @param  $data     array  Array with the group data
 * @return $success  mixed  ID of the changed group or FALSE on error
 */
function editGroup($data) {
	global $DB;
	global $config;

	$id     = (int) $data["id"];
	$name   = $data["name"];
	$info   = $data["info"];
	$params = array(
		"name"    => $name,
		"info"    => $info,
	);

	// Edit a group or Add a group?
	switch ($id) {
		// Add
		case 0:
			$sql = "INSERT INTO `groups` (`group_name`, `group_info`) VALUES (:name, :info)";
		break;

		// Edit
		default:
			$sql = "UPDATE `groups` SET `group_name` = :name, `group_info` = :info WHERE `group_id` = :id";
			$params["id"] = $id;
		break;
	}

	// Insert into / update the database
	$result = $DB->PreparedStatement($sql, $params, false, false);

	// Error
	if ($result === false || is_null($result)) {
		return false;
	}

	// Set the group id if new group
	$id = ($id > 0) ? $id : $DB->lastInsertId();


	// Return the id of added/edited group on success
	return $id;
}



/**
 * Set the state of a client (enabled, disabled)
 * @param  $id      int  ID of the group
 * @param  $state   int  0/1 (disabled/enabled)
 * @return $update  int  Number of updated rows (1 on success, 0 on failure)
 */
function setGroupState($id, $state) {
	global $DB;
	$id = (int) $id;
	$sql = "UPDATE `groups` SET `enabled` = :state WHERE `id` = :id";
	$params = array(
		"id"    => $id,
		"state" => $state
	);
	$update = $DB->PreparedStatement($sql, $params, false, false);

	return $update;
}



/**
 * Delete a group
 * @param  int $id      The ID of the group
 * @return int $success Number of deleted rows (n on success, 0 on failure)
 */
function deleteGroup($id) {
	global $DB;
	$id = (int)$id;

	$sql  = "DELETE `groups`.*, `group_members`.*  \n";
	$sql .= "FROM `groups` \n";
	$sql .= "LEFT JOIN `group_members` ON (`group_members`.`group_id` = `groups`.`group_id`) \n";
	$sql .= "WHERE `groups`.`group_id` = :id ";
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
?>
